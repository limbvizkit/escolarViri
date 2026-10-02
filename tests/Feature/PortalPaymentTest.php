<?php

namespace Tests\Feature;

use App\Models\GradoEscolar;
use App\Models\PagoOnline;
use App\Models\PortalUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PortalPaymentTest extends TestCase
{
    use RefreshDatabase;

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
    }

    private function portalUser(): PortalUser
    {
        $grado = GradoEscolar::factory()->create();

        return PortalUser::factory()->create([
            'grado_escolar_id' => $grado->id,
            'must_change_password' => false,
        ]);
    }

    public function test_guest_is_redirected_from_payment_form(): void
    {
        $this->get(route('portal.payments.create'))->assertRedirect(route('portal.login'));
    }

    public function test_authenticated_user_can_access_payment_form(): void
    {
        $user = $this->portalUser();

        $this->actingAs($user, 'portal')
            ->get(route('portal.payments.create'))
            ->assertOk()
            ->assertSee('Realizar pago')
            ->assertSee('order_id');
    }

    public function test_user_must_change_password_before_paying(): void
    {
        $grado = GradoEscolar::factory()->create();
        $user = PortalUser::factory()->create([
            'grado_escolar_id' => $grado->id,
            'must_change_password' => true,
        ]);

        $this->actingAs($user, 'portal')
            ->get(route('portal.payments.create'))
            ->assertRedirect(route('portal.password.change'));
    }

    public function test_successful_payment_creates_completed_payment(): void
    {
        Http::fake([
            'https://sandbox-api.openpay.mx/v1/merchant-test/charges' => Http::response([
                'id' => 'charge-test-123',
                'status' => 'completed',
                'authorization' => 'auth-123',
                'card' => [
                    'brand' => 'visa',
                    'card_number' => '411111XXXXXX1111',
                ],
            ], 200),
        ]);

        $user = $this->portalUser();

        $response = $this->actingAs($user, 'portal')
            ->post(route('portal.payments.store'), [
                'amount' => '150.50',
                'concepto' => 'Colegiatura marzo',
                'token_id' => 'token-123',
                'device_session_id' => 'device-123',
                'order_id' => 'PO-20260922-120000-ABC123',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pagos_online', [
            'portal_user_id' => $user->id,
            'concepto' => 'Colegiatura marzo',
            'monto' => 150.50,
            'moneda' => 'MXN',
            'order_id' => 'PO-20260922-120000-ABC123',
            'openpay_charge_id' => 'charge-test-123',
            'estatus' => PagoOnline::STATUS_COMPLETED,
            'authorization' => 'auth-123',
            'card_brand' => 'visa',
            'card_last4' => '1111',
        ]);

        Http::assertSent(function ($request) use ($user) {
            $body = json_decode($request->body(), true);
            $this->assertSame('token-123', $body['source_id']);
            $this->assertEquals(150.50, $body['amount']);
            $this->assertSame('MXN', $body['currency']);
            $this->assertSame('Colegiatura marzo', $body['description']);
            $this->assertSame('PO-20260922-120000-ABC123', $body['order_id']);
            $this->assertSame('device-123', $body['device_session_id']);
            $this->assertSame($user->name, $body['customer']['name']);
            $this->assertSame($user->email, $body['customer']['email']);
            $this->assertArrayNotHasKey('card_number', $body);
            $this->assertArrayNotHasKey('cvv', $body);
            $this->assertArrayNotHasKey('cvv2', $body);

            return true;
        });
    }

    public function test_payment_form_never_persists_raw_card_data(): void
    {
        Http::fake([
            'https://sandbox-api.openpay.mx/v1/merchant-test/charges' => Http::response([
                'id' => 'charge-test-456',
                'status' => 'completed',
                'card' => [
                    'brand' => 'mastercard',
                    'card_number' => '555555XXXXXX4444',
                ],
            ], 200),
        ]);

        $user = $this->portalUser();

        $this->actingAs($user, 'portal')
            ->post(route('portal.payments.store'), [
                'amount' => '200.00',
                'concepto' => 'Inscripción',
                'token_id' => 'token-456',
                'device_session_id' => 'device-456',
                'order_id' => 'PO-20260922-120001-DEF456',
                'card_number' => '5555555555554444',
                'cvv' => '123',
                'cvv2' => '123',
                'expiration_month' => '12',
                'expiration_year' => '25',
            ]);

        $this->assertDatabaseMissing('pagos_online', [
            'concepto' => '5555555555554444',
        ]);

        $this->assertDatabaseHas('pagos_online', [
            'order_id' => 'PO-20260922-120001-DEF456',
            'concepto' => 'Inscripción',
            'estatus' => PagoOnline::STATUS_COMPLETED,
        ]);
    }

    public function test_openpay_error_creates_failed_payment_and_shows_safe_message(): void
    {
        Http::fake([
            'https://sandbox-api.openpay.mx/v1/merchant-test/charges' => Http::response([
                'error_code' => 3001,
                'error_category' => 'request',
                'error_message' => 'The card was declined by the bank',
                'http_code' => 402,
            ], 402),
        ]);

        $user = $this->portalUser();

        $response = $this->actingAs($user, 'portal')
            ->post(route('portal.payments.store'), [
                'amount' => '100.00',
                'concepto' => 'Mensualidad',
                'token_id' => 'token-declined',
                'device_session_id' => 'device-789',
                'order_id' => 'PO-20260922-120002-GHI789',
            ]);

        $response->assertRedirect(route('portal.payments.create'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('pagos_online', [
            'portal_user_id' => $user->id,
            'order_id' => 'PO-20260922-120002-GHI789',
            'estatus' => PagoOnline::STATUS_FAILED,
            'error_code' => '3001',
            'error_category' => 'request',
            'error_message' => 'The card was declined by the bank',
        ]);

        $this->followRedirects($response)
            ->assertSee('No pudimos procesar el pago')
            ->assertDontSee('declined by the bank');
    }

    public function test_user_cannot_view_other_users_payments(): void
    {
        $grado = GradoEscolar::factory()->create();
        $userA = PortalUser::factory()->create(['grado_escolar_id' => $grado->id, 'must_change_password' => false]);
        $userB = PortalUser::factory()->create(['grado_escolar_id' => $grado->id, 'must_change_password' => false]);

        $paymentB = PagoOnline::factory()->create([
            'portal_user_id' => $userB->id,
            'order_id' => 'PO-20260922-120003-JKL012',
        ]);

        $this->actingAs($userA, 'portal')
            ->get(route('portal.payments.show', $paymentB))
            ->assertForbidden();
    }

    public function test_user_sees_only_own_payment_history(): void
    {
        $grado = GradoEscolar::factory()->create();
        $userA = PortalUser::factory()->create(['grado_escolar_id' => $grado->id, 'must_change_password' => false]);
        $userB = PortalUser::factory()->create(['grado_escolar_id' => $grado->id, 'must_change_password' => false]);

        PagoOnline::factory()->create([
            'portal_user_id' => $userA->id,
            'concepto' => 'Pago A',
            'order_id' => 'PO-20260922-120004-MNO345',
        ]);

        PagoOnline::factory()->create([
            'portal_user_id' => $userB->id,
            'concepto' => 'Pago B',
            'order_id' => 'PO-20260922-120005-PQR678',
        ]);

        $response = $this->actingAs($userA, 'portal')
            ->get(route('portal.payments.index'));

        $response->assertOk();
        $response->assertSee('Pago A');
        $response->assertDontSee('Pago B');
    }

    public function test_validation_rules_are_enforced(): void
    {
        $user = $this->portalUser();

        $this->actingAs($user, 'portal')
            ->post(route('portal.payments.store'), [
                'amount' => '',
                'concepto' => '',
                'token_id' => '',
                'device_session_id' => '',
                'order_id' => '',
            ])
            ->assertSessionHasErrors(['amount', 'concepto', 'token_id', 'device_session_id', 'order_id']);

        $this->actingAs($user, 'portal')
            ->post(route('portal.payments.store'), [
                'amount' => '0',
                'concepto' => 'AB',
                'token_id' => 'token',
                'device_session_id' => 'device',
                'order_id' => 'order',
            ])
            ->assertSessionHasErrors(['amount', 'concepto']);

        $this->actingAs($user, 'portal')
            ->post(route('portal.payments.store'), [
                'amount' => '100001',
                'concepto' => str_repeat('a', 256),
                'token_id' => 'token',
                'device_session_id' => 'device',
                'order_id' => 'order',
            ])
            ->assertSessionHasErrors(['amount', 'concepto']);
    }

    public function test_duplicate_submission_is_handled_via_order_id(): void
    {
        Http::fake([
            'https://sandbox-api.openpay.mx/v1/merchant-test/charges' => Http::response([
                'id' => 'charge-test-789',
                'status' => 'completed',
            ], 200),
        ]);

        $user = $this->portalUser();
        $orderId = 'PO-20260922-120006-STU901';

        $this->actingAs($user, 'portal')
            ->post(route('portal.payments.store'), [
                'amount' => '50.00',
                'concepto' => 'Material',
                'token_id' => 'token-789',
                'device_session_id' => 'device-789',
                'order_id' => $orderId,
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('pagos_online', 1);

        $this->actingAs($user, 'portal')
            ->post(route('portal.payments.store'), [
                'amount' => '50.00',
                'concepto' => 'Material',
                'token_id' => 'token-789',
                'device_session_id' => 'device-789',
                'order_id' => $orderId,
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('pagos_online', 1);
        Http::assertSentCount(1);
    }
}
