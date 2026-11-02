<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\FormaPago;
use App\Models\Pago;
use App\Models\Pago8am;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pago8amTest extends TestCase
{
    use RefreshDatabase;

    private function registrarPago8am(User $user, Alumno $alumno, string $mes, string $monto): void
    {
        $this->actingAs($user)->post(route('pagos-8am.store'), [
            'alumno_id' => $alumno->id,
            'mes' => $mes,
            'monto' => $monto,
        ])->assertSessionHasNoErrors();
    }

    public function test_lista_los_pagos_8am_con_fecha_de_registro(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create(['nombre' => 'Ana', 'apellido_paterno' => 'Garcia']);

        $pago = Pago8am::create([
            'alumno_id' => $alumno->id,
            'mes' => '2026-03',
            'monto' => 400,
            'observaciones' => 'Entrada de marzo',
        ]);

        $response = $this->actingAs($user)->get(route('pagos-8am.index'));

        $response->assertOk();
        $response->assertSee($alumno->nombre_completo);
        $response->assertSee(Pago::mesLabel('2026-03'));
        $response->assertSee('$400.00');
        $response->assertSee('Entrada de marzo');
        $response->assertSee($pago->created_at->format('d/m/Y'));
    }

    public function test_crea_un_pago_8am_y_actualiza_el_campo_entrada_8am_de_pagos(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();

        $this->registrarPago8am($user, $alumno, '2026-06', '300');

        $this->assertDatabaseHas('pagos_8am', [
            'alumno_id' => $alumno->id,
            'mes' => '2026-06',
            'monto' => '300.00',
        ]);

        $this->assertDatabaseHas('pagos', [
            'alumno_id' => $alumno->id,
            'mes' => '2026-06',
            'entrada_8am' => '300.00',
        ]);
    }

    public function test_guarda_efectivo_por_defecto_como_forma_de_pago(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $efectivo = FormaPago::create(['nombre' => 'Efectivo']);
        $transferencia = FormaPago::create(['nombre' => 'Transferencia']);

        $this->actingAs($user)->post(route('pagos-8am.store'), [
            'alumno_id' => $alumno->id,
            'mes' => '2026-07',
            'monto' => '300',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('pagos_8am', [
            'alumno_id' => $alumno->id,
            'mes' => '2026-07',
            'forma_pago_id' => $efectivo->id,
        ]);

        $this->actingAs($user)->post(route('pagos-8am.store'), [
            'alumno_id' => $alumno->id,
            'mes' => '2026-08',
            'monto' => '300',
            'forma_pago_id' => $transferencia->id,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('pagos_8am', [
            'alumno_id' => $alumno->id,
            'mes' => '2026-08',
            'forma_pago_id' => $transferencia->id,
        ]);
    }

    public function test_suma_los_pagos_8am_del_mes_en_el_campo_entrada_8am(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $mes = '2026-06';

        $this->registrarPago8am($user, $alumno, $mes, '300');
        $this->registrarPago8am($user, $alumno, $mes, '200');

        // Un solo pago mensual con la sumatoria de ambos registros.
        $this->assertDatabaseCount('pagos_8am', 2);
        $this->assertDatabaseCount('pagos', 1);
        $this->assertDatabaseHas('pagos', [
            'alumno_id' => $alumno->id,
            'mes' => $mes,
            'entrada_8am' => '500.00',
        ]);
    }

    public function test_al_eliminar_un_pago_8am_recalcula_el_campo_entrada_8am(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $mes = '2026-06';

        $this->registrarPago8am($user, $alumno, $mes, '300');
        $this->registrarPago8am($user, $alumno, $mes, '200');

        $primero = Pago8am::orderBy('id')->firstOrFail();
        $segundo = Pago8am::orderByDesc('id')->firstOrFail();

        $this->actingAs($user)->delete(route('pagos-8am.destroy', $primero))->assertRedirect(route('pagos-8am.index'));

        $this->assertDatabaseHas('pagos', [
            'alumno_id' => $alumno->id,
            'mes' => $mes,
            'entrada_8am' => '200.00',
        ]);

        $this->actingAs($user)->delete(route('pagos-8am.destroy', $segundo))->assertRedirect(route('pagos-8am.index'));

        $this->assertDatabaseHas('pagos', [
            'alumno_id' => $alumno->id,
            'mes' => $mes,
            'entrada_8am' => null,
        ]);
    }

    public function test_el_formulario_avisa_cuando_ya_existe_un_registro_del_mes(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();

        Pago8am::create([
            'alumno_id' => $alumno->id,
            'mes' => '2026-06',
            'monto' => 300,
        ]);

        $response = $this->actingAs($user)->get(route('pagos-8am.create'));

        $response->assertOk();
        $response->assertSee('Desea guardarlo?', false);
        $response->assertSee($alumno->id.'|2026-06');
    }

    public function test_profesor_no_puede_acceder_a_pagos_8am(): void
    {
        $user = User::factory()->profesor()->create();

        $this->actingAs($user)->get(route('pagos-8am.index'))->assertForbidden();
        $this->actingAs($user)->get(route('pagos-8am.create'))->assertForbidden();
    }

    public function test_formulario_preselecciona_efectivo_por_defecto(): void
    {
        $user = User::factory()->admin()->create();
        $efectivo = FormaPago::create(['nombre' => 'Efectivo']);

        $response = $this->actingAs($user)->get(route('pagos-8am.create'));

        $response->assertOk();

        $html = preg_replace('/\s+/', ' ', (string) $response->getContent());

        $this->assertStringContainsString(
            'value="'.$efectivo->id.'" selected',
            $html,
            'El formulario debe preseleccionar Efectivo por defecto.'
        );
    }
}
