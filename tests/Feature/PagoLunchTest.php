<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Pago;
use App\Models\PagoLunch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagoLunchTest extends TestCase
{
    use RefreshDatabase;

    private function registrarPagoLunch(User $user, Alumno $alumno, string $mes, string $monto): void
    {
        $this->actingAs($user)->post(route('pagos-lunch.store'), [
            'alumno_id' => $alumno->id,
            'mes' => $mes,
            'monto' => $monto,
        ])->assertSessionHasNoErrors();
    }

    public function test_lista_los_pagos_de_lunch_con_fecha_de_registro(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create(['nombre' => 'Ana', 'apellido_paterno' => 'Garcia']);

        $pago = PagoLunch::create([
            'alumno_id' => $alumno->id,
            'mes' => '2026-03',
            'monto' => 400,
            'observaciones' => 'Lunch de marzo',
        ]);

        $response = $this->actingAs($user)->get(route('pagos-lunch.index'));

        $response->assertOk();
        $response->assertSee($alumno->nombre_completo);
        $response->assertSee(Pago::mesLabel('2026-03'));
        $response->assertSee('$400.00');
        $response->assertSee('Lunch de marzo');
        $response->assertSee($pago->created_at->format('d/m/Y'));
    }

    public function test_crea_un_pago_de_lunch_y_actualiza_el_campo_lunch_de_pagos(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();

        $this->registrarPagoLunch($user, $alumno, '2026-06', '300');

        $this->assertDatabaseHas('pagos_lunch', [
            'alumno_id' => $alumno->id,
            'mes' => '2026-06',
            'monto' => '300.00',
        ]);

        $this->assertDatabaseHas('pagos', [
            'alumno_id' => $alumno->id,
            'mes' => '2026-06',
            'lunch' => '300.00',
        ]);
    }

    public function test_suma_los_pagos_de_lunch_del_mes_en_el_campo_lunch(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $mes = '2026-06';

        $this->registrarPagoLunch($user, $alumno, $mes, '300');
        $this->registrarPagoLunch($user, $alumno, $mes, '200');

        // Un solo pago mensual con la sumatoria de ambos registros.
        $this->assertDatabaseCount('pagos_lunch', 2);
        $this->assertDatabaseCount('pagos', 1);
        $this->assertDatabaseHas('pagos', [
            'alumno_id' => $alumno->id,
            'mes' => $mes,
            'lunch' => '500.00',
        ]);
    }

    public function test_al_eliminar_un_pago_de_lunch_recalcula_el_campo_lunch(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $mes = '2026-06';

        $this->registrarPagoLunch($user, $alumno, $mes, '300');
        $this->registrarPagoLunch($user, $alumno, $mes, '200');

        $primero = PagoLunch::orderBy('id')->firstOrFail();
        $segundo = PagoLunch::orderByDesc('id')->firstOrFail();

        $this->actingAs($user)->delete(route('pagos-lunch.destroy', $primero))->assertRedirect(route('pagos-lunch.index'));

        $this->assertDatabaseHas('pagos', [
            'alumno_id' => $alumno->id,
            'mes' => $mes,
            'lunch' => '200.00',
        ]);

        $this->actingAs($user)->delete(route('pagos-lunch.destroy', $segundo))->assertRedirect(route('pagos-lunch.index'));

        $this->assertDatabaseHas('pagos', [
            'alumno_id' => $alumno->id,
            'mes' => $mes,
            'lunch' => null,
        ]);
    }

    public function test_el_formulario_avisa_cuando_ya_existe_un_registro_del_mes(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();

        PagoLunch::create([
            'alumno_id' => $alumno->id,
            'mes' => '2026-06',
            'monto' => 300,
        ]);

        $response = $this->actingAs($user)->get(route('pagos-lunch.create'));

        $response->assertOk();
        $response->assertSee('Desea guardarlo?', false);
        $response->assertSee($alumno->id.'|2026-06');
    }

    public function test_profesor_no_puede_acceder_a_pagos_de_lunch(): void
    {
        $user = User::factory()->profesor()->create();

        $this->actingAs($user)->get(route('pagos-lunch.index'))->assertForbidden();
        $this->actingAs($user)->get(route('pagos-lunch.create'))->assertForbidden();
    }
}
