<?php

namespace Tests\Unit\Services;

use App\Exceptions\OpenpayException;
use App\Services\OpenpayService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenpayServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        Config::set('openpay.merchant_id', 'merchant-test');
        Config::set('openpay.public_key', 'pk-test');
        Config::set('openpay.secret_key', 'sk-test-secret');
        Config::set('openpay.mode', 'sandbox');
        Config::set('openpay.timeout', 15);
        Config::set('openpay.urls.sandbox', 'https://sandbox-api.openpay.mx/v1/{merchant_id}');
        Config::set('openpay.urls.production', 'https://api.openpay.mx/v1/{merchant_id}');
    }

    public function test_creates_charge_successfully(): void
    {
        Http::fake([
            'https://sandbox-api.openpay.mx/v1/merchant-test/charges' => Http::response([
                'id' => 'charge-id',
                'status' => 'completed',
                'amount' => 100.00,
            ], 200),
        ]);

        $service = new OpenpayService;
        $result = $service->createCharge([
            'source_id' => 'token-123',
            'amount' => 100.00,
            'currency' => 'MXN',
            'description' => 'Test payment',
            'order_id' => 'order-123',
            'device_session_id' => 'device-123',
            'customer' => ['name' => 'Test Customer'],
        ]);

        $this->assertSame('charge-id', $result['id']);
        $this->assertSame('completed', $result['status']);

        Http::assertSent(function (Request $request) {
            $this->assertSame('POST', $request->method());
            $this->assertSame('https://sandbox-api.openpay.mx/v1/merchant-test/charges', (string) $request->url());

            $authorization = $request->header('Authorization');
            $this->assertNotEmpty($authorization);
            $this->assertStringStartsWith('Basic ', $authorization[0]);
            $this->assertSame('sk-test-secret:', base64_decode(substr($authorization[0], 6)));

            $body = json_decode($request->body(), true);
            $this->assertSame('card', $body['method']);
            $this->assertSame('token-123', $body['source_id']);
            $this->assertEquals(100.00, $body['amount']);
            $this->assertSame('MXN', $body['currency']);
            $this->assertArrayNotHasKey('secret_key', $body);
            $this->assertArrayNotHasKey('public_key', $body);

            return true;
        });
    }

    public function test_uses_production_url_when_mode_is_production(): void
    {
        Config::set('openpay.mode', 'production');

        Http::fake([
            'https://api.openpay.mx/v1/merchant-test/charges' => Http::response(['id' => 'charge-prod'], 200),
        ]);

        $service = new OpenpayService;
        $service->createCharge(['source_id' => 'token-456', 'amount' => 50.00]);

        Http::assertSent(fn (Request $request) => $request->url() === 'https://api.openpay.mx/v1/merchant-test/charges');
    }

    public function test_throws_typed_exception_on_openpay_error(): void
    {
        Http::fake([
            'https://sandbox-api.openpay.mx/v1/merchant-test/charges' => Http::response([
                'error_code' => 1001,
                'category' => 'request',
                'description' => 'Invalid card token',
                'http_code' => 422,
            ], 422),
        ]);

        $this->expectException(OpenpayException::class);
        $this->expectExceptionMessage('Invalid card token');

        try {
            (new OpenpayService)->createCharge([
                'source_id' => 'bad-token',
                'amount' => 100.00,
            ]);
        } catch (OpenpayException $e) {
            $this->assertSame('1001', $e->getErrorCode());
            $this->assertSame('request', $e->getCategory());
            $this->assertSame(422, $e->getHttpStatus());

            throw $e;
        }
    }

    public function test_throws_typed_exception_on_openpay_error_with_category_and_message_fields(): void
    {
        Http::fake([
            'https://sandbox-api.openpay.mx/v1/merchant-test/charges' => Http::response([
                'error_code' => 3001,
                'error_category' => 'request',
                'error_message' => 'The card was declined',
                'http_code' => 402,
            ], 402),
        ]);

        $this->expectException(OpenpayException::class);
        $this->expectExceptionMessage('The card was declined');

        try {
            (new OpenpayService)->createCharge([
                'source_id' => 'bad-token',
                'amount' => 100.00,
            ]);
        } catch (OpenpayException $e) {
            $this->assertSame('3001', $e->getErrorCode());
            $this->assertSame('request', $e->getCategory());
            $this->assertSame(402, $e->getHttpStatus());

            throw $e;
        }
    }

    public function test_throws_typed_exception_on_connection_error(): void
    {
        Http::fake(fn (Request $request) => throw new ConnectionException('cURL error 28: Operation timed out'));

        $this->expectException(OpenpayException::class);
        $this->expectExceptionMessage('Unable to connect to OpenPay');

        try {
            (new OpenpayService)->createCharge([
                'source_id' => 'token-789',
                'amount' => 100.00,
            ]);
        } catch (OpenpayException $e) {
            $this->assertSame('connection', $e->getCategory());
            $this->assertNull($e->getErrorCode());

            throw $e;
        }
    }

    public function test_throws_configuration_exception_when_merchant_id_is_missing(): void
    {
        Config::set('openpay.merchant_id', '');

        $this->expectException(OpenpayException::class);
        $this->expectExceptionMessage('OpenPay merchant ID and secret key are required.');

        try {
            (new OpenpayService)->createCharge(['source_id' => 'token', 'amount' => 10.00]);
        } catch (OpenpayException $e) {
            $this->assertSame('configuration', $e->getCategory());
            $this->assertNull($e->getHttpStatus());

            throw $e;
        }
    }

    public function test_throws_configuration_exception_when_secret_key_is_missing(): void
    {
        Config::set('openpay.secret_key', null);

        $this->expectException(OpenpayException::class);
        $this->expectExceptionMessage('OpenPay merchant ID and secret key are required.');

        try {
            (new OpenpayService)->createCharge(['source_id' => 'token', 'amount' => 10.00]);
        } catch (OpenpayException $e) {
            $this->assertSame('configuration', $e->getCategory());

            throw $e;
        }
    }
}
