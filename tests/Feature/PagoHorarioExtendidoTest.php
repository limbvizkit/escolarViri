<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Estatus;
use App\Models\HorarioExtendido;
use App\Models\Pago;
use App\Models\PagoHorarioExtendido;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagoHorarioExtendidoTest extends TestCase
{
    use RefreshDatabase;

    private function registrarPago(User $user, Alumno $alumno, HorarioExtendido $horario, string $mes, string $monto): void
    {
        $this->actingAs($user)->post(route('pagos-horarios-extendidos.store'), [
            'alumno_id' => $alumno->id,
            'horario_extendido_id' => $horario->id,
            'mes' => $mes,
            'monto' => $monto,
        ])->assertSessionHasNoErrors();
    }

    public function test_lista_los_pagos_con_fecha_de_registro(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create(['nombre' => 'Ana', 'apellido_paterno' => 'Garcia']);
        $horario = HorarioExtendido::create(['nombre' => 'Matutino']);

        $pago = PagoHorarioExtendido::create([
            'alumno_id' => $alumno->id,
            'horario_extendido_id' => $horario->id,
            'mes' => '2026-03',
            'monto' => 400,
            'observaciones' => 'Horario de marzo',
        ]);

        $response = $this->actingAs($user)->get(route('pagos-horarios-extendidos.index'));

        $response->assertOk();
        $response->assertSee($alumno->nombre_completo);
        $response->assertSee('Matutino');
        $response->assertSee(Pago::mesLabel('2026-03'));
        $response->assertSee('$400.00');
        $response->assertSee('Horario de marzo');
        $response->assertSee($pago->created_at->format('d/m/Y'));
    }

    public function test_crea_un_pago_y_actualiza_el_campo_horario_extendido_de_pagos(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $horario = HorarioExtendido::create(['nombre' => 'Vespertino']);

        $this->registrarPago($user, $alumno, $horario, '2026-06', '300');

        $this->assertDatabaseHas('pagos_horarios_extendidos', [
            'alumno_id' => $alumno->id,
            'horario_extendido_id' => $horario->id,
            'mes' => '2026-06',
            'monto' => '300.00',
        ]);

        $this->assertDatabaseHas('pagos', [
            'alumno_id' => $alumno->id,
            'mes' => '2026-06',
            'horario_extendido' => '300.00',
        ]);
    }

    public function test_suma_los_pagos_del_mes_en_el_campo_horario_extendido(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $horarioA = HorarioExtendido::create(['nombre' => 'Matutino']);
        $horarioB = HorarioExtendido::create(['nombre' => 'Vespertino']);
        $mes = '2026-06';

        $this->registrarPago($user, $alumno, $horarioA, $mes, '300');
        $this->registrarPago($user, $alumno, $horarioB, $mes, '200');

        $this->assertDatabaseCount('pagos_horarios_extendidos', 2);
        $this->assertDatabaseCount('pagos', 1);
        $this->assertDatabaseHas('pagos', [
            'alumno_id' => $alumno->id,
            'mes' => $mes,
            'horario_extendido' => '500.00',
        ]);
    }

    public function test_al_eliminar_un_pago_recalcula_el_campo_horario_extendido(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $horarioA = HorarioExtendido::create(['nombre' => 'Matutino']);
        $horarioB = HorarioExtendido::create(['nombre' => 'Vespertino']);
        $mes = '2026-06';

        $this->registrarPago($user, $alumno, $horarioA, $mes, '300');
        $this->registrarPago($user, $alumno, $horarioB, $mes, '200');

        $primero = PagoHorarioExtendido::orderBy('id')->firstOrFail();
        $segundo = PagoHorarioExtendido::orderByDesc('id')->firstOrFail();

        $this->actingAs($user)->delete(route('pagos-horarios-extendidos.destroy', $primero))
            ->assertRedirect(route('pagos-horarios-extendidos.index'));

        $this->assertDatabaseHas('pagos', [
            'alumno_id' => $alumno->id,
            'mes' => $mes,
            'horario_extendido' => '200.00',
        ]);

        $this->actingAs($user)->delete(route('pagos-horarios-extendidos.destroy', $segundo))
            ->assertRedirect(route('pagos-horarios-extendidos.index'));

        $this->assertDatabaseHas('pagos', [
            'alumno_id' => $alumno->id,
            'mes' => $mes,
            'horario_extendido' => null,
        ]);
    }

    public function test_el_formulario_avisa_cuando_ya_existe_un_registro_del_mes(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $horario = HorarioExtendido::create(['nombre' => 'Matutino']);

        PagoHorarioExtendido::create([
            'alumno_id' => $alumno->id,
            'horario_extendido_id' => $horario->id,
            'mes' => '2026-06',
            'monto' => 300,
        ]);

        $response = $this->actingAs($user)->get(route('pagos-horarios-extendidos.create'));

        $response->assertOk();
        $response->assertSee('Desea guardarlo?', false);
        $response->assertSee($alumno->id.'|2026-06');
    }

    public function test_el_formulario_muestra_solo_horarios_activos(): void
    {
        $user = User::factory()->admin()->create();
        HorarioExtendido::create(['nombre' => 'Matutino']);
        HorarioExtendido::create(['nombre' => 'Nocturno', 'estatus_id' => Estatus::INACTIVO]);

        $response = $this->actingAs($user)->get(route('pagos-horarios-extendidos.create'));

        $response->assertOk();
        $response->assertSee('Matutino');
        $response->assertDontSee('Nocturno');
    }

    public function test_rechaza_un_horario_extendido_inactivo(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $inactivo = HorarioExtendido::create(['nombre' => 'Nocturno', 'estatus_id' => Estatus::INACTIVO]);

        $response = $this->actingAs($user)->post(route('pagos-horarios-extendidos.store'), [
            'alumno_id' => $alumno->id,
            'horario_extendido_id' => $inactivo->id,
            'mes' => '2026-06',
            'monto' => '300',
        ]);

        $response->assertSessionHasErrors('horario_extendido_id');
        $this->assertDatabaseCount('pagos_horarios_extendidos', 0);
    }

    public function test_profesor_no_puede_acceder_a_pagos_de_horario_extendido(): void
    {
        $user = User::factory()->profesor()->create();

        $this->actingAs($user)->get(route('pagos-horarios-extendidos.index'))->assertForbidden();
        $this->actingAs($user)->get(route('pagos-horarios-extendidos.create'))->assertForbidden();
    }
}
