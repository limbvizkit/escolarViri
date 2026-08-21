<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\FormaPago;
use App\Models\GradoEscolar;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrecargarPagosTest extends TestCase
{
    use RefreshDatabase;

    protected string $mesActual;

    protected string $mesSiguiente;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mesActual = now()->format('Y-m');
        $this->mesSiguiente = now()->startOfMonth()->addMonthNoOverflow()->format('Y-m');
    }

    public function test_crea_los_pagos_seleccionados_para_el_mes_siguiente(): void
    {
        $grado = GradoEscolar::create(['nombre' => 'Primaria', 'slug' => 'primaria']);
        $forma = FormaPago::create(['nombre' => 'Efectivo']);

        $alumnoA = Alumno::create([
            'grado_escolar_id' => $grado->id,
            'nombre' => 'Ana',
            'apellido_paterno' => 'Garcia',
        ]);

        $alumnoB = Alumno::create([
            'grado_escolar_id' => $grado->id,
            'nombre' => 'Bruno',
            'apellido_paterno' => 'Lopez',
        ]);

        $pagoA = Pago::create([
            'alumno_id' => $alumnoA->id,
            'mes' => $this->mesActual,
            'fecha' => '2026-08-31',
            'entrada_8am' => 120,
            'pronto_pago' => 120,
            'forma_pago_id' => $forma->id,
        ]);

        $pagoB = Pago::create([
            'alumno_id' => $alumnoB->id,
            'mes' => $this->mesActual,
            'pago_normal' => 300,
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->post(route('pagos.precargar.store'), [
                'seleccionados' => [$pagoA->id, $pagoB->id],
                'pagos' => [
                    // Solo un monto modificado; el resto usa los valores precargados.
                    $pagoA->id => ['pronto_pago' => '150'],
                ],
            ]);

        $response->assertRedirect(route('pagos.index'));
        $response->assertSessionHas('success', fn (string $mensaje) => str_contains($mensaje, 'Se crearon 2 pagos para '.Pago::mesLabel($this->mesSiguiente)));

        $this->assertDatabaseCount('pagos', 4);

        $nuevoA = Pago::query()
            ->where('alumno_id', $alumnoA->id)
            ->where('mes', $this->mesSiguiente)
            ->firstOrFail();

        $this->assertSame('150.00', (string) $nuevoA->pronto_pago);
        $this->assertSame('120.00', (string) $nuevoA->entrada_8am);
        $this->assertSame('2026-09-30', $nuevoA->fecha?->format('Y-m-d'));
        $this->assertSame($forma->id, $nuevoA->forma_pago_id);

        $this->assertDatabaseHas('pagos', [
            'alumno_id' => $alumnoB->id,
            'mes' => $this->mesSiguiente,
        ]);
    }

    public function test_omite_alumnos_que_ya_tienen_pago_en_el_mes_siguiente(): void
    {
        $grado = GradoEscolar::create(['nombre' => 'Primaria', 'slug' => 'primaria']);

        $alumnoA = Alumno::create([
            'grado_escolar_id' => $grado->id,
            'nombre' => 'Ana',
            'apellido_paterno' => 'Garcia',
        ]);

        $alumnoB = Alumno::create([
            'grado_escolar_id' => $grado->id,
            'nombre' => 'Bruno',
            'apellido_paterno' => 'Lopez',
        ]);

        $pagoA = Pago::create([
            'alumno_id' => $alumnoA->id,
            'mes' => $this->mesActual,
            'pronto_pago' => 120,
        ]);

        $pagoB = Pago::create([
            'alumno_id' => $alumnoB->id,
            'mes' => $this->mesActual,
            'pronto_pago' => 130,
        ]);

        Pago::create([
            'alumno_id' => $alumnoB->id,
            'mes' => $this->mesSiguiente,
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->post(route('pagos.precargar.store'), [
                'seleccionados' => [$pagoA->id, $pagoB->id],
            ]);

        $response->assertRedirect(route('pagos.index'));
        $response->assertSessionHas('success', function (string $mensaje) use ($alumnoB) {
            return str_contains($mensaje, 'Se crearon 1 pagos')
                && str_contains($mensaje, 'Se omitieron 1: '.$alumnoB->nombre_completo);
        });

        $this->assertDatabaseCount('pagos', 4);

        $this->assertSame(1, Pago::query()
            ->where('alumno_id', $alumnoB->id)
            ->where('mes', $this->mesSiguiente)
            ->count());

        $this->assertDatabaseHas('pagos', [
            'alumno_id' => $alumnoA->id,
            'mes' => $this->mesSiguiente,
        ]);
    }

    public function test_muestra_la_pagina_con_los_pagos_del_mes_actual(): void
    {
        $grado = GradoEscolar::create(['nombre' => 'Primaria', 'slug' => 'primaria']);

        $alumno = Alumno::create([
            'grado_escolar_id' => $grado->id,
            'nombre' => 'Ana',
            'apellido_paterno' => 'Garcia',
        ]);

        Pago::create([
            'alumno_id' => $alumno->id,
            'mes' => $this->mesActual,
            'pronto_pago' => 120,
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('pagos.precargar'));

        $response->assertOk();
        $response->assertViewIs('pagos.precargar');
        $response->assertSee('Ana Garcia');
        $response->assertSee('Pagos de '.Pago::mesLabel($this->mesActual));
        $response->assertSee('Guardar seleccionados');
    }
}
