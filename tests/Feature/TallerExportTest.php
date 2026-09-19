<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Taller;
use App\Models\TallerAlumno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TallerExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_listado_muestra_links_de_exportacion(): void
    {
        $user = User::factory()->create();
        $taller = Taller::create(['nombre' => 'Pintura', 'costo' => 500]);
        $alumno = Alumno::factory()->create();
        TallerAlumno::create([
            'taller_id' => $taller->id,
            'alumno_id' => $alumno->id,
            'hora_inicio' => '10:00',
            'hora_fin' => '12:00',
            'monto_pagado' => 250,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('talleres.index'));

        $response->assertOk();

        $html = $response->getContent();

        $this->assertStringContainsString(route('talleres.export.pdf'), $html);
        $this->assertStringContainsString(route('talleres.export.excel'), $html);
    }

    public function test_puede_descargar_pdf_de_talleres(): void
    {
        $user = User::factory()->create();
        $taller = Taller::create(['nombre' => 'Pintura', 'costo' => 500]);
        $alumno = Alumno::factory()->create();
        TallerAlumno::create([
            'taller_id' => $taller->id,
            'alumno_id' => $alumno->id,
            'hora_inicio' => '10:00',
            'hora_fin' => '12:00',
            'monto_pagado' => 250,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('talleres.export.pdf'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_puede_descargar_excel_de_talleres(): void
    {
        $user = User::factory()->create();
        $taller = Taller::create(['nombre' => 'Pintura', 'costo' => 500]);
        $alumno = Alumno::factory()->create();
        TallerAlumno::create([
            'taller_id' => $taller->id,
            'alumno_id' => $alumno->id,
            'hora_inicio' => '10:00',
            'hora_fin' => '12:00',
            'monto_pagado' => 250,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('talleres.export.excel'));

        $response->assertOk();
        $response->assertHeader(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
    }
}
