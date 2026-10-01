<?php

namespace Tests\Feature;

use App\Models\GradoEscolar;
use App\Models\PagoOnline;
use App\Models\PortalUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class OnlinePaymentAdminTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->admin()->create();
    }

    private function portalUser(): PortalUser
    {
        $grado = GradoEscolar::factory()->create();

        return PortalUser::factory()->create([
            'grado_escolar_id' => $grado->id,
            'must_change_password' => false,
        ]);
    }

    public function test_admin_can_list_online_payments(): void
    {
        $admin = $this->adminUser();
        $user = $this->portalUser();
        $payment = PagoOnline::factory()->create([
            'portal_user_id' => $user->id,
            'concepto' => 'Colegiatura marzo',
            'estatus' => PagoOnline::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('online-payments.index'));

        $response->assertOk();
        $response->assertSee('Pagos en línea');
        $response->assertSee($payment->concepto);
        $response->assertSee($user->name);
    }

    public function test_list_filters_by_status(): void
    {
        $admin = $this->adminUser();
        $user = $this->portalUser();

        PagoOnline::factory()->create([
            'portal_user_id' => $user->id,
            'concepto' => 'Pago completado',
            'estatus' => PagoOnline::STATUS_COMPLETED,
        ]);

        PagoOnline::factory()->create([
            'portal_user_id' => $user->id,
            'concepto' => 'Pago fallido',
            'estatus' => PagoOnline::STATUS_FAILED,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('online-payments.index', ['estatus' => PagoOnline::STATUS_FAILED]));

        $response->assertOk();
        $response->assertSee('Pago fallido');
        $response->assertDontSee('Pago completado');
    }

    public function test_list_filters_by_date_range(): void
    {
        $admin = $this->adminUser();
        $user = $this->portalUser();

        $oldPayment = PagoOnline::factory()->create([
            'portal_user_id' => $user->id,
            'concepto' => 'Pago antiguo',
            'created_at' => now()->subDays(10),
        ]);

        $newPayment = PagoOnline::factory()->create([
            'portal_user_id' => $user->id,
            'concepto' => 'Pago reciente',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('online-payments.index', [
                'fecha_desde' => now()->subDay()->format('Y-m-d'),
                'fecha_hasta' => now()->addDay()->format('Y-m-d'),
            ]));

        $response->assertOk();
        $response->assertSee('Pago reciente');
        $response->assertDontSee('Pago antiguo');
    }

    public function test_search_filters_by_concept_user_order_and_charge(): void
    {
        $admin = $this->adminUser();
        $userA = $this->portalUser();
        $userB = $this->portalUser();

        PagoOnline::factory()->create([
            'portal_user_id' => $userA->id,
            'concepto' => 'Inscripción especial',
            'order_id' => 'PO-SEARCH-123',
            'openpay_charge_id' => 'charge-search',
        ]);

        PagoOnline::factory()->create([
            'portal_user_id' => $userB->id,
            'concepto' => 'Otro pago',
        ]);

        $this->actingAs($admin)
            ->get(route('online-payments.index', ['q' => 'Inscripción']))
            ->assertOk()
            ->assertSee('Inscripción especial')
            ->assertDontSee('Otro pago');

        $this->actingAs($admin)
            ->get(route('online-payments.index', ['q' => 'PO-SEARCH-123']))
            ->assertOk()
            ->assertSee('Inscripción especial')
            ->assertDontSee('Otro pago');

        $this->actingAs($admin)
            ->get(route('online-payments.index', ['q' => 'charge-search']))
            ->assertOk()
            ->assertSee('Inscripción especial')
            ->assertDontSee('Otro pago');

        $this->actingAs($admin)
            ->get(route('online-payments.index', ['q' => $userA->email]))
            ->assertOk()
            ->assertSee('Inscripción especial')
            ->assertDontSee('Otro pago');
    }

    public function test_admin_can_view_payment_detail(): void
    {
        $admin = $this->adminUser();
        $user = $this->portalUser();
        $payment = PagoOnline::factory()->create([
            'portal_user_id' => $user->id,
            'concepto' => 'Colegiatura abril',
            'openpay_charge_id' => 'charge-123',
            'authorization' => 'auth-123',
            'card_brand' => 'visa',
            'card_last4' => '1111',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('online-payments.show', $payment));

        $response->assertOk();
        $response->assertSee($payment->concepto);
        $response->assertSee('charge-123');
        $response->assertSee('auth-123');
        $response->assertSee('Visa');
        $response->assertSee('1111');
        $response->assertSee($user->name);
    }

    public function test_show_does_not_expose_sensitive_data(): void
    {
        $admin = $this->adminUser();
        $user = $this->portalUser();
        $payment = PagoOnline::factory()->create([
            'portal_user_id' => $user->id,
            'concepto' => 'Pago secreto',
            'error_message' => 'Tarjeta robada',
            'error_code' => '3002',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('online-payments.show', $payment));

        $response->assertOk();
        $response->assertSee('Pago secreto');
        $response->assertDontSee('Tarjeta robada');
        $response->assertDontSee('3002');
    }

    public function test_portal_user_cannot_access_admin_routes(): void
    {
        $portalUser = $this->portalUser();
        $payment = PagoOnline::factory()->create(['portal_user_id' => $portalUser->id]);

        $this->actingAs($portalUser, 'portal');
        Auth::shouldUse('web');

        $this->get(route('online-payments.index'))->assertRedirect(route('login'));
        $this->get(route('online-payments.show', $payment))->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $payment = PagoOnline::factory()->create(['portal_user_id' => $this->portalUser()->id]);

        $this->get(route('online-payments.index'))->assertRedirect(route('login'));
        $this->get(route('online-payments.show', $payment))->assertRedirect(route('login'));
    }

    public function test_export_pdf_requires_auth_and_applies_filters(): void
    {
        $admin = $this->adminUser();
        $user = $this->portalUser();

        PagoOnline::factory()->create([
            'portal_user_id' => $user->id,
            'concepto' => 'Pago PDF',
            'estatus' => PagoOnline::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('online-payments.export.pdf', ['q' => 'Pago PDF']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_export_excel_requires_auth_and_applies_filters(): void
    {
        $admin = $this->adminUser();
        $user = $this->portalUser();

        PagoOnline::factory()->create([
            'portal_user_id' => $user->id,
            'concepto' => 'Pago Excel',
            'estatus' => PagoOnline::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('online-payments.export.excel', ['q' => 'Pago Excel']));

        $response->assertOk();
        $response->assertHeader(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
    }

    public function test_export_links_preserve_filters(): void
    {
        $admin = $this->adminUser();
        $user = $this->portalUser();

        PagoOnline::factory()->create([
            'portal_user_id' => $user->id,
            'concepto' => 'Filtro export',
            'estatus' => PagoOnline::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('online-payments.index', [
                'q' => 'Filtro export',
                'estatus' => PagoOnline::STATUS_COMPLETED,
                'fecha_desde' => now()->format('Y-m-d'),
            ]));

        $response->assertOk();

        $html = $response->getContent();

        $this->assertStringContainsString(
            str_replace('&', '&amp;', route('online-payments.export.pdf', [
                'q' => 'Filtro export',
                'estatus' => PagoOnline::STATUS_COMPLETED,
                'fecha_desde' => now()->format('Y-m-d'),
            ])),
            $html
        );

        $this->assertStringContainsString(
            str_replace('&', '&amp;', route('online-payments.export.excel', [
                'q' => 'Filtro export',
                'estatus' => PagoOnline::STATUS_COMPLETED,
                'fecha_desde' => now()->format('Y-m-d'),
            ])),
            $html
        );
    }
}
