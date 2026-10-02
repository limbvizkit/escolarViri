<?php

namespace App\Http\Controllers;

use App\Models\PagoOnline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OpenpayWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $authResult = $this->authenticate($request);

        if ($authResult === 'missing_config') {
            return response()->json(['message' => 'Service Unavailable'], 503);
        }

        if ($authResult === 'invalid') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $payload = $request->json()->all();

        if (! is_array($payload) || $payload === []) {
            return response()->json(['message' => 'Invalid JSON'], 400);
        }

        $eventType = $this->extractEventType($payload);
        $transaction = $this->extractTransaction($payload);
        $chargeId = $this->extractChargeId($transaction);
        $orderId = $transaction['order_id'] ?? null;

        if ($eventType === null || $chargeId === null) {
            return response()->json(['message' => 'Ignored'], 200);
        }

        $newStatus = $this->resolveStatus($eventType, $transaction['status'] ?? null);

        if ($newStatus === null) {
            return response()->json(['message' => 'Ignored'], 200);
        }

        $payment = $this->findPayment($chargeId, $orderId);

        if ($payment === null) {
            return response()->json(['message' => 'Processed'], 200);
        }

        if ($this->shouldUpdate($payment, $newStatus)) {
            $payment->update($this->safeUpdateData($newStatus, $chargeId, $transaction));
        }

        return response()->json(['message' => 'Processed'], 200);
    }

    private function authenticate(Request $request): string
    {
        $expectedUser = config('openpay.webhook_user');
        $expectedPassword = config('openpay.webhook_password');

        if (empty($expectedUser) || empty($expectedPassword)) {
            return 'missing_config';
        }

        $authorization = $request->header('Authorization');

        if (! is_string($authorization) || ! Str::startsWith($authorization, 'Basic ')) {
            return 'invalid';
        }

        $decoded = base64_decode(Str::after($authorization, 'Basic '), true);

        if ($decoded === false) {
            return 'invalid';
        }

        $parts = explode(':', $decoded, 2);
        $user = $parts[0] ?? '';
        $password = $parts[1] ?? '';

        if (! hash_equals($expectedUser, $user) || ! hash_equals($expectedPassword, $password)) {
            return 'invalid';
        }

        return 'ok';
    }

    private function extractEventType(array $payload): ?string
    {
        $type = $payload['type'] ?? $payload['event_type'] ?? null;

        return is_string($type) && $type !== '' ? $type : null;
    }

    private function extractTransaction(array $payload): array
    {
        if (isset($payload['transaction']) && is_array($payload['transaction'])) {
            return $payload['transaction'];
        }

        if (isset($payload['charge']) && is_array($payload['charge'])) {
            return $payload['charge'];
        }

        return $payload;
    }

    private function extractChargeId(array $transaction): ?string
    {
        $id = $transaction['id'] ?? $transaction['transaction_id'] ?? $transaction['charge_id'] ?? null;

        return is_string($id) && $id !== '' ? $id : null;
    }

    private function resolveStatus(string $eventType, mixed $transactionStatus): ?string
    {
        $eventMap = [
            'charge.completed' => PagoOnline::STATUS_COMPLETED,
            'charge.succeeded' => PagoOnline::STATUS_COMPLETED,
            'charge.failed' => PagoOnline::STATUS_FAILED,
            'charge.cancelled' => PagoOnline::STATUS_FAILED,
            'charge.refunded' => PagoOnline::STATUS_FAILED,
            'charge.created' => PagoOnline::STATUS_PENDING,
            'charge.pending' => PagoOnline::STATUS_PENDING,
        ];

        if (isset($eventMap[$eventType])) {
            return $eventMap[$eventType];
        }

        $status = is_string($transactionStatus) ? $transactionStatus : '';

        return match ($status) {
            'completed', 'succeeded' => PagoOnline::STATUS_COMPLETED,
            'failed', 'cancelled', 'refunded' => PagoOnline::STATUS_FAILED,
            'created', 'pending' => PagoOnline::STATUS_PENDING,
            default => null,
        };
    }

    private function findPayment(string $chargeId, ?string $orderId): ?PagoOnline
    {
        $query = PagoOnline::query();

        $query->where(function ($q) use ($chargeId, $orderId) {
            $q->where('openpay_charge_id', $chargeId);

            if ($orderId !== null && $orderId !== '') {
                $q->orWhere('order_id', $orderId);
            }
        });

        return $query->first();
    }

    private function shouldUpdate(PagoOnline $payment, string $newStatus): bool
    {
        if ($payment->estatus === $newStatus) {
            return true;
        }

        if ($newStatus === PagoOnline::STATUS_PENDING) {
            return false;
        }

        if ($payment->estatus === PagoOnline::STATUS_FAILED && $newStatus === PagoOnline::STATUS_COMPLETED) {
            return false;
        }

        return true;
    }

    private function safeUpdateData(string $newStatus, string $chargeId, array $transaction): array
    {
        $data = [
            'estatus' => $newStatus,
            'openpay_charge_id' => $chargeId,
        ];

        if (! empty($transaction['authorization']) && is_string($transaction['authorization'])) {
            $data['authorization'] = $transaction['authorization'];
        }

        $card = is_array($transaction['card'] ?? null) ? $transaction['card'] : [];

        if (! empty($card['brand']) && is_string($card['brand'])) {
            $data['card_brand'] = $card['brand'];
        }

        $lastFour = $this->extractLastFour($card['card_number'] ?? null);
        if ($lastFour !== null) {
            $data['card_last4'] = $lastFour;
        }

        return $data;
    }

    private function extractLastFour(mixed $cardNumber): ?string
    {
        if (! is_string($cardNumber) || $cardNumber === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', $cardNumber);

        if ($digits === null || strlen($digits) < 4) {
            return null;
        }

        return substr($digits, -4);
    }
}
