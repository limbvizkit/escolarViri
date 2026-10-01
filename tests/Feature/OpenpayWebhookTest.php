<?php

namespace Tests\Feature;

use App\Models\GradoEscolar;
use App\Models\PagoOnline;
use App\Models\PortalUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class OpenpayWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('openpay.webhook_user', 'webhook-user');
        Config::set('openpay.webhook_password', 'webhook-pass');
    }

    private function portalUser(): PortalUser
    {
        $grado = GradoEscolar::factory()->create();

        return PortalUser::factory()->create([
            'grado_escolar_id' => $grado->id,
            'must_change_password' => false,
        ]);
    }

    private function basicHeader(string $user, string $pass): array
    {
        return [
            'Authorization' => 'Basic '.base64_encode($user.':'.$pass),
            'Accept' => 'application/json',
        ];
    }

    private function validPayload(string $eventType, string $status, string $chargeId, ?string $orderId = null): array
    {
        return [
            'type' => $eventType,
            'transaction' => [
                'id' => $chargeId,
                'order_id' => $orderId,
                'status' => $status,
                'authorization' => 'auth-123',
                'card' => [
                    'brand' => 'visa',
                    'card_number' => '411111XXXXXX1111',
                ],
            ],
        ];
    }

    public function test_missing_configuration_returns_503(): void
    {
        Config::set('openpay.webhook_user', '');
        Config::set('openpay.webhook_password', '');

        $this->postJson(route('openpay.webhook'), $this->validPayload('charge.completed', 'completed', 'charge-1'))
            ->assertStatus(503)
            ->assertJson(['message' => 'Service Unavailable']);
    }

    public function test_missing_authorization_header_returns_401(): void
    {
        $this->postJson(route('openpay.webhook'), $this->validPayload('charge.completed', 'completed', 'charge-1'))
            ->assertUnauthorized()
            ->assertJson(['message' => 'Unauthorized']);
    }

    public function test_invalid_basic_auth_returns_401(): void
    {
        $this->postJson(
            route('openpay.webhook'),
            $this->validPayload('charge.completed', 'completed', 'charge-1'),
            $this->basicHeader('webhook-user', 'wrong-pass'),
        )
            ->assertUnauthorized()
            ->assertJson(['message' => 'Unauthorized']);
    }

    public function test_completed_event_updates_payment_to_completed(): void
    {
        $user = $this->portalUser();
        $payment = PagoOnline::factory()->create([
            'portal_user_id' => $user->id,
            'order_id' => 'PO-20260922-120000-ABC123',
            'openpay_charge_id' => null,
            'estatus' => PagoOnline::STATUS_PENDING,
        ]);

        $this->postJson(
            route('openpay.webhook'),
            $this->validPayload('charge.completed', 'completed', 'charge-123', $payment->order_id),
            $this->basicHeader('webhook-user', 'webhook-pass'),
        )
            ->assertOk()
            ->assertJson(['message' => 'Processed']);

        $payment->refresh();
        $this->assertSame(PagoOnline::STATUS_COMPLETED, $payment->estatus);
        $this->assertSame('charge-123', $payment->openpay_charge_id);
        $this->assertSame('auth-123', $payment->authorization);
        $this->assertSame('visa', $payment->card_brand);
        $this->assertSame('1111', $payment->card_last4);
    }

    public function test_failed_event_updates_payment_to_failed(): void
    {
        $user = $this->portalUser();
        $payment = PagoOnline::factory()->create([
            'portal_user_id' => $user->id,
            'order_id' => 'PO-20260922-120001-DEF456',
            'openpay_charge_id' => 'charge-456',
            'estatus' => PagoOnline::STATUS_PENDING,
        ]);

        $this->postJson(
            route('openpay.webhook'),
            $this->validPayload('charge.failed', 'failed', 'charge-456', $payment->order_id),
            $this->basicHeader('webhook-user', 'webhook-pass'),
        )
            ->assertOk()
            ->assertJson(['message' => 'Processed']);

        $this->assertSame(PagoOnline::STATUS_FAILED, $payment->fresh()->estatus);
    }

    public function test_pending_event_updates_payment_to_pending(): void
    {
        $user = $this->portalUser();
        $payment = PagoOnline::factory()->create([
            'portal_user_id' => $user->id,
            'order_id' => 'PO-20260922-120002-GHI789',
            'openpay_charge_id' => 'charge-789',
            'estatus' => PagoOnline::STATUS_PENDING,
        ]);

        $this->postJson(
            route('openpay.webhook'),
            $this->validPayload('charge.pending', 'pending', 'charge-789', $payment->order_id),
            $this->basicHeader('webhook-user', 'webhook-pass'),
        )
            ->assertOk();

        $this->assertSame(PagoOnline::STATUS_PENDING, $payment->fresh()->estatus);
    }

    public function test_repeated_webhook_is_idempotent(): void
    {
        $user = $this->portalUser();
        $payment = PagoOnline::factory()->create([
            'portal_user_id' => $user->id,
            'order_id' => 'PO-20260922-120003-JKL012',
            'openpay_charge_id' => 'charge-012',
            'estatus' => PagoOnline::STATUS_PENDING,
        ]);

        $payload = $this->validPayload('charge.completed', 'completed', 'charge-012', $payment->order_id);
        $headers = $this->basicHeader('webhook-user', 'webhook-pass');

        $this->postJson(route('openpay.webhook'), $payload, $headers)->assertOk();
        $this->postJson(route('openpay.webhook'), $payload, $headers)->assertOk();

        $this->assertDatabaseCount('pagos_online', 1);
        $this->assertSame(PagoOnline::STATUS_COMPLETED, $payment->fresh()->estatus);
    }

    public function test_pending_event_does_not_downgrade_completed_payment(): void
    {
        $user = $this->portalUser();
        $payment = PagoOnline::factory()->create([
            'portal_user_id' => $user->id,
            'order_id' => 'PO-20260922-120004-MNO345',
            'openpay_charge_id' => 'charge-345',
            'estatus' => PagoOnline::STATUS_COMPLETED,
        ]);

        $this->postJson(
            route('openpay.webhook'),
            $this->validPayload('charge.pending', 'pending', 'charge-345', $payment->order_id),
            $this->basicHeader('webhook-user', 'webhook-pass'),
        )
            ->assertOk();

        $this->assertSame(PagoOnline::STATUS_COMPLETED, $payment->fresh()->estatus);
    }

    public function test_completed_event_does_not_update_failed_payment(): void
    {
        $user = $this->portalUser();
        $payment = PagoOnline::factory()->create([
            'portal_user_id' => $user->id,
            'order_id' => 'PO-20260922-120005-PQR678',
            'openpay_charge_id' => 'charge-678',
            'estatus' => PagoOnline::STATUS_FAILED,
        ]);

        $this->postJson(
            route('openpay.webhook'),
            $this->validPayload('charge.completed', 'completed', 'charge-678', $payment->order_id),
            $this->basicHeader('webhook-user', 'webhook-pass'),
        )
            ->assertOk();

        $this->assertSame(PagoOnline::STATUS_FAILED, $payment->fresh()->estatus);
    }

    public function test_unknown_event_is_ignored(): void
    {
        $user = $this->portalUser();
        $payment = PagoOnline::factory()->create([
            'portal_user_id' => $user->id,
            'order_id' => 'PO-20260922-120006-STU901',
            'openpay_charge_id' => 'charge-901',
            'estatus' => PagoOnline::STATUS_PENDING,
        ]);

        $this->postJson(
            route('openpay.webhook'),
            [
                'type' => 'charge.unknown',
                'transaction' => [
                    'id' => 'charge-901',
                    'order_id' => $payment->order_id,
                    'status' => 'unknown',
                ],
            ],
            $this->basicHeader('webhook-user', 'webhook-pass'),
        )
            ->assertOk()
            ->assertJson(['message' => 'Ignored']);

        $this->assertSame(PagoOnline::STATUS_PENDING, $payment->fresh()->estatus);
    }

    public function test_no_orphan_payment_is_created(): void
    {
        $this->postJson(
            route('openpay.webhook'),
            $this->validPayload('charge.completed', 'completed', 'charge-orphan', 'PO-UNKNOWN-000000'),
            $this->basicHeader('webhook-user', 'webhook-pass'),
        )
            ->assertOk()
            ->assertJson(['message' => 'Processed']);

        $this->assertDatabaseCount('pagos_online', 0);
    }

    public function test_invalid_json_returns_400(): void
    {
        $headers = $this->basicHeader('webhook-user', 'webhook-pass');
        $server = collect($headers)->mapWithKeys(fn ($value, $key) => ['HTTP_'.str_replace('-', '_', $key) => $value])->all();
        $server['CONTENT_TYPE'] = 'application/json';

        $this->call('POST', route('openpay.webhook'), [], [], [], $server, 'not-json')
            ->assertStatus(400)
            ->assertJson(['message' => 'Invalid JSON']);
    }

    public function test_event_type_alias_is_supported(): void
    {
        $user = $this->portalUser();
        $payment = PagoOnline::factory()->create([
            'portal_user_id' => $user->id,
            'order_id' => 'PO-20260922-120007-VWX234',
            'openpay_charge_id' => 'charge-234',
            'estatus' => PagoOnline::STATUS_PENDING,
        ]);

        $this->postJson(
            route('openpay.webhook'),
            [
                'event_type' => 'charge.succeeded',
                'charge' => [
                    'transaction_id' => 'charge-234',
                    'order_id' => $payment->order_id,
                    'status' => 'succeeded',
                    'authorization' => 'auth-alias',
                    'card' => [
                        'brand' => 'mastercard',
                        'card_number' => '555555XXXXXX4444',
                    ],
                ],
            ],
            $this->basicHeader('webhook-user', 'webhook-pass'),
        )
            ->assertOk();

        $payment->refresh();
        $this->assertSame(PagoOnline::STATUS_COMPLETED, $payment->estatus);
        $this->assertSame('auth-alias', $payment->authorization);
        $this->assertSame('mastercard', $payment->card_brand);
        $this->assertSame('4444', $payment->card_last4);
    }

    public function test_refunded_event_updates_completed_to_failed(): void
    {
        $user = $this->portalUser();
        $payment = PagoOnline::factory()->create([
            'portal_user_id' => $user->id,
            'order_id' => 'PO-20260922-120008-YZAB567',
            'openpay_charge_id' => 'charge-567',
            'estatus' => PagoOnline::STATUS_COMPLETED,
        ]);

        $this->postJson(
            route('openpay.webhook'),
            $this->validPayload('charge.refunded', 'refunded', 'charge-567', $payment->order_id),
            $this->basicHeader('webhook-user', 'webhook-pass'),
        )
            ->assertOk();

        $this->assertSame(PagoOnline::STATUS_FAILED, $payment->fresh()->estatus);
    }
}
