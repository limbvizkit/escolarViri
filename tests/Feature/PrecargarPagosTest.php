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

        $response->assertRedirect(route('pagos.precargar'));
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

        $response->assertRedirect(route('pagos.precargar'));
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

        $html = $response->getContent();

        // La tabla inferior tiene clase de zebra, está envuelta en un scroll interno y hay dos botones de guardar.
        $this->assertStringContainsString('class="table ip-table ip-table-zebra mb-0" id="pagos-actuales-table"', $html);
        $this->assertStringContainsString('class="ip-table-scroll"', $html);
        $this->assertSame(2, substr_count($html, 'Guardar seleccionados'));
    }

    public function test_la_tabla_superior_no_muestra_la_columna_numero_y_usa_badge_por_forma_de_pago(): void
    {
        $grado = GradoEscolar::create(['nombre' => 'Primaria', 'slug' => 'primaria']);
        $forma = FormaPago::create(['nombre' => 'Efectivo']);

        $alumno = Alumno::create([
            'grado_escolar_id' => $grado->id,
            'nombre' => 'Ana',
            'apellido_paterno' => 'Garcia',
        ]);

        Pago::create([
            'alumno_id' => $alumno->id,
            'mes' => $this->mesSiguiente,
            'pronto_pago' => 120,
            'forma_pago_id' => $forma->id,
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('pagos.precargar'));

        $response->assertOk();

        $html = $response->getContent();
        preg_match('/<table class="table ip-table ip-table-zebra mb-0" id="pagos-siguientes-table">(.*?)<\/table>/s', $html, $matchesSuperior);
        $tablaSuperior = $matchesSuperior[1] ?? '';

        $this->assertStringNotContainsString('<th>#</th>', $tablaSuperior);
        $this->assertStringNotContainsString('>#<', $tablaSuperior);
        $this->assertStringContainsString('ip-badge-forma-pago', $tablaSuperior);
        $this->assertStringContainsString('ip-forma-pago-'.$forma->id, $tablaSuperior);
        $this->assertStringContainsString('ip-forma-pago-efectivo', $tablaSuperior);
    }

    public function test_permite_en_la_vista_precargar_cuando_no_se_crea_ningun_pago(): void
    {
        $grado = GradoEscolar::create(['nombre' => 'Primaria', 'slug' => 'primaria']);

        $alumno = Alumno::create([
            'grado_escolar_id' => $grado->id,
            'nombre' => 'Ana',
            'apellido_paterno' => 'Garcia',
        ]);

        $pago = Pago::create([
            'alumno_id' => $alumno->id,
            'mes' => $this->mesActual,
            'pronto_pago' => 120,
        ]);

        Pago::create([
            'alumno_id' => $alumno->id,
            'mes' => $this->mesSiguiente,
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->post(route('pagos.precargar.store'), [
                'seleccionados' => [$pago->id],
            ]);

        $response->assertRedirect(route('pagos.precargar'));
        $response->assertSessionHas('error', fn (string $mensaje) => str_contains($mensaje, 'No se crearon pagos nuevos'));

        $this->assertDatabaseCount('pagos', 2);
    }

    public function test_excluye_de_la_tabla_inferior_a_alumnos_ya_presentes_en_la_superior(): void
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

        Pago::create([
            'alumno_id' => $alumnoA->id,
            'mes' => $this->mesActual,
            'pronto_pago' => 120,
        ]);

        Pago::create([
            'alumno_id' => $alumnoB->id,
            'mes' => $this->mesActual,
            'pronto_pago' => 130,
        ]);

        Pago::create([
            'alumno_id' => $alumnoB->id,
            'mes' => $this->mesSiguiente,
            'pronto_pago' => 140,
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('pagos.precargar'));

        $response->assertOk();
        $response->assertSee('Pagos ya registrados para '.Pago::mesLabel($this->mesSiguiente));
        $response->assertSee($alumnoB->nombre_completo);

        $html = $response->getContent();
        preg_match('/<table class="table ip-table ip-table-zebra mb-0" id="pagos-actuales-table">(.*?)<\/table>/s', $html, $matches);
        $tablaInferior = $matches[1] ?? '';

        $this->assertStringContainsString($alumnoA->nombre_completo, $tablaInferior);
        $this->assertStringNotContainsString($alumnoB->nombre_completo, $tablaInferior);
    }

    public function test_permite_editar_en_linea_los_pagos_del_mes_siguiente(): void
    {
        $grado = GradoEscolar::create(['nombre' => 'Primaria', 'slug' => 'primaria']);
        $formaEfectivo = FormaPago::create(['nombre' => 'Efectivo']);
        $formaTransferencia = FormaPago::create(['nombre' => 'Transferencia']);

        $alumno = Alumno::create([
            'grado_escolar_id' => $grado->id,
            'nombre' => 'Ana',
            'apellido_paterno' => 'Garcia',
        ]);

        $pago = Pago::create([
            'alumno_id' => $alumno->id,
            'mes' => $this->mesSiguiente,
            'fecha' => '2026-09-15',
            'pronto_pago' => 100,
            'forma_pago_id' => $formaEfectivo->id,
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->put(route('pagos.inline-update', $pago), [
                'mes' => $this->mesSiguiente,
                'fecha' => '2026-09-20',
                'pronto_pago' => '250',
                'pago_normal' => '300',
                'forma_pago_id' => $formaTransferencia->id,
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('valores.pronto_pago', '250.00');
        $response->assertJsonPath('valores.pago_normal', '300.00');
        $response->assertJsonPath('valores.forma_pago_id', $formaTransferencia->id);

        $this->assertDatabaseHas('pagos', [
            'id' => $pago->id,
            'pronto_pago' => '250.00',
            'pago_normal' => '300.00',
            'forma_pago_id' => $formaTransferencia->id,
        ]);
        $this->assertSame('2026-09-20', $pago->fresh()->fecha?->format('Y-m-d'));
    }

    public function test_rechaza_edicion_inline_que_genera_pago_duplicado(): void
    {
        $grado = GradoEscolar::create(['nombre' => 'Primaria', 'slug' => 'primaria']);

        $alumno = Alumno::create([
            'grado_escolar_id' => $grado->id,
            'nombre' => 'Ana',
            'apellido_paterno' => 'Garcia',
        ]);

        $pagoActual = Pago::create([
            'alumno_id' => $alumno->id,
            'mes' => $this->mesActual,
            'pronto_pago' => 100,
        ]);

        Pago::create([
            'alumno_id' => $alumno->id,
            'mes' => $this->mesSiguiente,
            'pronto_pago' => 150,
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->put(route('pagos.inline-update', $pagoActual), [
                'mes' => $this->mesSiguiente,
                'pronto_pago' => '200',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('mensaje', 'No se pudo guardar: ya existe un pago de ese mes para el alumno seleccionado.');

        $this->assertDatabaseHas('pagos', [
            'id' => $pagoActual->id,
            'mes' => $this->mesActual,
            'pronto_pago' => '100.00',
        ]);
    }

    public function test_rechaza_edicion_inline_con_mes_invalido(): void
    {
        $grado = GradoEscolar::create(['nombre' => 'Primaria', 'slug' => 'primaria']);

        $alumno = Alumno::create([
            'grado_escolar_id' => $grado->id,
            'nombre' => 'Ana',
            'apellido_paterno' => 'Garcia',
        ]);

        $pago = Pago::create([
            'alumno_id' => $alumno->id,
            'mes' => $this->mesSiguiente,
            'pronto_pago' => 100,
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->putJson(route('pagos.inline-update', $pago), ['mes' => 'no-valido']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['mes']);
    }
}
