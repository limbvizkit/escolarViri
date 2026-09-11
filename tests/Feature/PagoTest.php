<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\GradoEscolar;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagoTest extends TestCase
{
    use RefreshDatabase;

    public function test_muestra_la_columna_grado_escolar_en_la_tabla(): void
    {
        $grado = GradoEscolar::create(['nombre' => 'Primaria', 'slug' => 'primaria']);
        $alumno = Alumno::create([
            'grado_escolar_id' => $grado->id,
            'nombre' => 'Ana',
            'apellido_paterno' => 'Garcia',
        ]);

        Pago::create([
            'alumno_id' => $alumno->id,
            'mes' => now()->format('Y-m'),
            'pronto_pago' => 120,
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('pagos.index'));

        $response->assertOk();
        $response->assertSee('Grado escolar');
        $response->assertSee($grado->nombre);
        $response->assertSee($alumno->nombre_completo);
    }

    public function test_filtro_por_grado_escolar_muestra_solo_los_pagos_de_ese_grado(): void
    {
        $gradoPrimaria = GradoEscolar::create(['nombre' => 'Primaria', 'slug' => 'primaria']);
        $gradoPreescolar = GradoEscolar::create(['nombre' => 'Preescolar', 'slug' => 'preescolar']);

        $alumnoPrimaria = Alumno::create([
            'grado_escolar_id' => $gradoPrimaria->id,
            'nombre' => 'Ana',
            'apellido_paterno' => 'Garcia',
        ]);

        $alumnoPreescolar = Alumno::create([
            'grado_escolar_id' => $gradoPreescolar->id,
            'nombre' => 'Bruno',
            'apellido_paterno' => 'Lopez',
        ]);

        Pago::create([
            'alumno_id' => $alumnoPrimaria->id,
            'mes' => now()->format('Y-m'),
            'pronto_pago' => 120,
        ]);

        Pago::create([
            'alumno_id' => $alumnoPreescolar->id,
            'mes' => now()->format('Y-m'),
            'pronto_pago' => 130,
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('pagos.index', ['grado_escolar_id' => $gradoPrimaria->id]));

        $response->assertOk();
        $response->assertSee($alumnoPrimaria->nombre_completo);
        $response->assertDontSee($alumnoPreescolar->nombre_completo);
    }

    public function test_el_grado_escolar_no_aparece_dentro_de_la_celda_alumno(): void
    {
        $grado = GradoEscolar::create(['nombre' => 'Primaria', 'slug' => 'primaria']);
        $alumno = Alumno::create([
            'grado_escolar_id' => $grado->id,
            'nombre' => 'Ana',
            'apellido_paterno' => 'Garcia',
        ]);

        Pago::create([
            'alumno_id' => $alumno->id,
            'mes' => now()->format('Y-m'),
            'pronto_pago' => 120,
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('pagos.index'));

        $response->assertOk();

        $html = $response->getContent();

        preg_match('/<table class="table ip-table mb-0" id="pagos-table">(.*?)<\/table>/s', $html, $matches);
        $tabla = $matches[1] ?? '';

        $this->assertStringContainsString($grado->nombre, $tabla);
        $this->assertSame(1, substr_count($tabla, $grado->nombre));
    }

    public function test_los_links_de_exportacion_incluyen_el_filtro_de_grado_escolar(): void
    {
        $grado = GradoEscolar::create(['nombre' => 'Primaria', 'slug' => 'primaria']);
        $alumno = Alumno::create([
            'grado_escolar_id' => $grado->id,
            'nombre' => 'Ana',
            'apellido_paterno' => 'Garcia',
        ]);

        Pago::create([
            'alumno_id' => $alumno->id,
            'mes' => now()->format('Y-m'),
            'pronto_pago' => 120,
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('pagos.index', ['grado_escolar_id' => $grado->id]));

        $response->assertOk();

        $html = $response->getContent();

        $this->assertStringContainsString(
            route('pagos.export.pdf', ['grado_escolar_id' => $grado->id]),
            $html
        );
        $this->assertStringContainsString(
            route('pagos.export.excel', ['grado_escolar_id' => $grado->id]),
            $html
        );
    }
}
