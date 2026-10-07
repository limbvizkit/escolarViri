<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Pago;
use App\Models\PagoTaller;
use App\Models\Taller;
use App\Models\TallerAlumno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagoTallerTest extends TestCase
{
    use RefreshDatabase;

    private function inscribir(Alumno $alumno, Taller $taller): void
    {
        TallerAlumno::create([
            'alumno_id' => $alumno->id,
            'taller_id' => $taller->id,
        ]);
    }

    private function registrarPagoTaller(User $user, Alumno $alumno, Taller $taller, string $mes, string $monto): void
    {
        $this->actingAs($user)->post(route('pagos-talleres.store'), [
            'alumno_id' => $alumno->id,
            'taller_id' => $taller->id,
            'mes' => $mes,
            'monto' => $monto,
        ])->assertSessionHasNoErrors();
    }

    public function test_lista_los_pagos_de_talleres_con_fecha_de_registro(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create(['nombre' => 'Ana', 'apellido_paterno' => 'Garcia']);
        $taller = Taller::create(['nombre' => 'Robótica', 'costo' => 500]);
        $this->inscribir($alumno, $taller);

        $pago = PagoTaller::create([
            'alumno_id' => $alumno->id,
            'taller_id' => $taller->id,
            'mes' => '2026-03',
            'monto' => 500,
            'observaciones' => 'Pago de marzo',
        ]);

        $response = $this->actingAs($user)->get(route('pagos-talleres.index'));

        $response->assertOk();
        $response->assertSee($alumno->nombre_completo);
        $response->assertSee('Robótica');
        $response->assertSee(Pago::mesLabel('2026-03'));
        $response->assertSee('$500.00');
        $response->assertSee('Pago de marzo');
        $response->assertSee($pago->created_at->format('d/m/Y'));
    }

    public function test_crea_un_pago_para_un_taller_inscrito(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $taller = Taller::create(['nombre' => 'Música', 'costo' => 300]);
        $this->inscribir($alumno, $taller);

        $response = $this->actingAs($user)->post(route('pagos-talleres.store'), [
            'alumno_id' => $alumno->id,
            'taller_id' => $taller->id,
            'mes' => '2026-04',
            'monto' => '300',
            'observaciones' => 'Abril',
        ]);

        $response->assertRedirect(route('pagos-talleres.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('pagos_talleres', [
            'alumno_id' => $alumno->id,
            'taller_id' => $taller->id,
            'mes' => '2026-04',
            'monto' => '300.00',
        ]);

        $this->assertNotNull(PagoTaller::first()->created_at);
    }

    public function test_rechaza_un_taller_no_inscrito_para_el_alumno(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $inscrito = Taller::create(['nombre' => 'Danza', 'costo' => 200]);
        $noInscrito = Taller::create(['nombre' => 'Ajedrez', 'costo' => 150]);
        $this->inscribir($alumno, $inscrito);

        $response = $this->actingAs($user)->post(route('pagos-talleres.store'), [
            'alumno_id' => $alumno->id,
            'taller_id' => $noInscrito->id,
            'mes' => '2026-04',
            'monto' => '150',
        ]);

        $response->assertSessionHasErrors('taller_id');
        $this->assertDatabaseCount('pagos_talleres', 0);
    }

    public function test_la_pagina_de_alta_solo_muestra_los_talleres_inscritos(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $inscrito = Taller::create(['nombre' => 'Pintura', 'costo' => 100]);
        $otro = Taller::create(['nombre' => 'Natación', 'costo' => 100]);
        $this->inscribir($alumno, $inscrito);

        $response = $this->actingAs($user)->get(route('pagos-talleres.create'));

        $response->assertOk();
        $response->assertSee('Pintura');
        $response->assertDontSee('Natación');
    }

    public function test_actualiza_un_pago_de_taller(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $taller = Taller::create(['nombre' => 'Teatro', 'costo' => 100]);
        $this->inscribir($alumno, $taller);

        $pago = PagoTaller::create([
            'alumno_id' => $alumno->id,
            'taller_id' => $taller->id,
            'mes' => '2026-05',
            'monto' => 100,
        ]);

        $response = $this->actingAs($user)->put(route('pagos-talleres.update', $pago), [
            'alumno_id' => $alumno->id,
            'taller_id' => $taller->id,
            'mes' => '2026-05',
            'monto' => '250',
            'observaciones' => 'Ajuste',
        ]);

        $response->assertRedirect(route('pagos-talleres.index'));

        $this->assertDatabaseHas('pagos_talleres', [
            'id' => $pago->id,
            'monto' => '250.00',
            'observaciones' => 'Ajuste',
        ]);
    }

    public function test_elimina_un_pago_de_taller(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $taller = Taller::create(['nombre' => 'Cocina', 'costo' => 100]);
        $this->inscribir($alumno, $taller);

        $pago = PagoTaller::create([
            'alumno_id' => $alumno->id,
            'taller_id' => $taller->id,
            'mes' => '2026-05',
            'monto' => 100,
        ]);

        $response = $this->actingAs($user)->delete(route('pagos-talleres.destroy', $pago));

        $response->assertRedirect(route('pagos-talleres.index'));
        $this->assertDatabaseCount('pagos_talleres', 0);
    }

    public function test_suma_los_pagos_de_talleres_en_el_campo_talleres_del_pago_mensual(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $tallerA = Taller::create(['nombre' => 'Fútbol', 'costo' => 100]);
        $tallerB = Taller::create(['nombre' => 'Música', 'costo' => 100]);
        $this->inscribir($alumno, $tallerA);
        $this->inscribir($alumno, $tallerB);

        $mes = '2026-06';

        $this->registrarPagoTaller($user, $alumno, $tallerA, $mes, '300');

        $this->assertDatabaseHas('pagos', [
            'alumno_id' => $alumno->id,
            'mes' => $mes,
            'talleres' => '300.00',
        ]);

        $this->registrarPagoTaller($user, $alumno, $tallerB, $mes, '200');

        // Un solo pago mensual con la sumatoria de ambos talleres.
        $this->assertDatabaseCount('pagos', 1);
        $this->assertDatabaseHas('pagos', [
            'alumno_id' => $alumno->id,
            'mes' => $mes,
            'talleres' => '500.00',
        ]);
    }

    public function test_al_eliminar_un_pago_de_taller_recalcula_el_campo_talleres(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $tallerA = Taller::create(['nombre' => 'Fútbol', 'costo' => 100]);
        $tallerB = Taller::create(['nombre' => 'Música', 'costo' => 100]);
        $this->inscribir($alumno, $tallerA);
        $this->inscribir($alumno, $tallerB);

        $mes = '2026-06';
        $this->registrarPagoTaller($user, $alumno, $tallerA, $mes, '300');
        $this->registrarPagoTaller($user, $alumno, $tallerB, $mes, '200');

        $pagoA = PagoTaller::where('alumno_id', $alumno->id)->where('taller_id', $tallerA->id)->firstOrFail();
        $pagoB = PagoTaller::where('alumno_id', $alumno->id)->where('taller_id', $tallerB->id)->firstOrFail();

        $this->actingAs($user)->delete(route('pagos-talleres.destroy', $pagoA))->assertRedirect(route('pagos-talleres.index'));

        $this->assertDatabaseHas('pagos', [
            'alumno_id' => $alumno->id,
            'mes' => $mes,
            'talleres' => '200.00',
        ]);

        $this->actingAs($user)->delete(route('pagos-talleres.destroy', $pagoB))->assertRedirect(route('pagos-talleres.index'));

        $this->assertDatabaseHas('pagos', [
            'alumno_id' => $alumno->id,
            'mes' => $mes,
            'talleres' => null,
        ]);
    }

    public function test_profesor_no_puede_acceder_a_pagos_de_talleres(): void
    {
        $user = User::factory()->profesor()->create();

        $this->actingAs($user)->get(route('pagos-talleres.index'))->assertForbidden();
        $this->actingAs($user)->get(route('pagos-talleres.create'))->assertForbidden();
    }
}
