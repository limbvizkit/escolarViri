<?php

namespace App\Services;

use App\Exceptions\OpenpayException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class OpenpayService
{
    /**
     * Create a card charge through the OpenPay REST API.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws OpenpayException
     */
    public function createCharge(array $data): array
    {
        $config = $this->resolveConfig();
        $url = rtrim($config['base_url'], '/').'/charges';

        try {
            $response = Http::withBasicAuth($config['secret_key'], '')
                ->timeout($config['timeout'])
                ->acceptJson()
                ->asJson()
                ->post($url, $this->preparePayload($data));
        } catch (ConnectionException $e) {
            throw OpenpayException::connection('Unable to connect to OpenPay: '.$e->getMessage());
        }

        if (! $response->successful()) {
            $this->handleFailedResponse($response);
        }

        return $response->json() ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveConfig(): array
    {
        $merchantId = (string) config('openpay.merchant_id');
        $secretKey = (string) config('openpay.secret_key');
        $mode = (string) config('openpay.mode', 'sandbox');
        $timeout = (int) config('openpay.timeout', 30);

        if ($merchantId === '' || $secretKey === '') {
            throw OpenpayException::configuration('OpenPay merchant ID and secret key are required.');
        }

        if (! in_array($mode, ['sandbox', 'production'], true)) {
            throw OpenpayException::configuration('OpenPay mode must be sandbox or production.');
        }

        $baseUrl = (string) config("openpay.urls.{$mode}", config('openpay.urls.sandbox'));
        $baseUrl = str_replace('{merchant_id}', $merchantId, $baseUrl);

        return [
            'merchant_id' => $merchantId,
            'secret_key' => $secretKey,
            'base_url' => $baseUrl,
            'timeout' => $timeout,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function preparePayload(array $data): array
    {
        return array_merge(['method' => 'card'], $data);
    }

    /**
     * @throws OpenpayException
     */
    private function handleFailedResponse(Response $response): never
    {
        $status = $response->status();
        $body = $response->json() ?? [];

        $errorCode = (string) ($body['error_code'] ?? $status);
        $errorCategory = (string) ($body['error_category'] ?? $body['category'] ?? 'request');
        $errorMessage = (string) ($body['error_message'] ?? $body['description'] ?? $response->reason());

        throw new OpenpayException($errorMessage, $errorCode, $errorCategory, $status);
    }
}
