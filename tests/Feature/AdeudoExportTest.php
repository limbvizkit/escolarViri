<?php

namespace Tests\Feature;

use App\Models\Adeudo;
use App\Models\Alumno;
use App\Models\Estatus;
use App\Models\GradoEscolar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdeudoExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_los_links_de_exportacion_incluyen_los_filtros_activos(): void
    {
        $user = User::factory()->create();
        $grado = GradoEscolar::factory()->create();
        $alumno = Alumno::factory()->create(['grado_escolar_id' => $grado->id]);
        Adeudo::create([
            'alumno_id' => $alumno->id,
            'concepto' => 'Colegiatura',
            'monto' => 1000,
            'monto_pagado' => 0,
            'estatus' => Adeudo::ESTATUS_PENDIENTE,
            'estatus_id' => Estatus::ACTIVO,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('adeudos.index', ['estatus' => Adeudo::ESTATUS_PENDIENTE, 'q' => 'Colegiatura']));

        $response->assertOk();

        $html = $response->getContent();

        $this->assertStringContainsString(
            str_replace('&', '&amp;', route('adeudos.export.pdf', ['q' => 'Colegiatura', 'estatus' => Adeudo::ESTATUS_PENDIENTE])),
            $html
        );
        $this->assertStringContainsString(
            str_replace('&', '&amp;', route('adeudos.export.excel', ['q' => 'Colegiatura', 'estatus' => Adeudo::ESTATUS_PENDIENTE])),
            $html
        );
    }

    public function test_puede_descargar_pdf_de_adeudos_filtrado(): void
    {
        $user = User::factory()->create();
        $grado = GradoEscolar::factory()->create();
        $alumno = Alumno::factory()->create(['grado_escolar_id' => $grado->id]);
        Adeudo::create([
            'alumno_id' => $alumno->id,
            'concepto' => 'Colegiatura',
            'monto' => 1000,
            'monto_pagado' => 0,
            'estatus' => Adeudo::ESTATUS_PENDIENTE,
            'estatus_id' => Estatus::ACTIVO,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('adeudos.export.pdf', ['estatus' => Adeudo::ESTATUS_PENDIENTE]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_puede_descargar_excel_de_adeudos_filtrado(): void
    {
        $user = User::factory()->create();
        $grado = GradoEscolar::factory()->create();
        $alumno = Alumno::factory()->create(['grado_escolar_id' => $grado->id]);
        Adeudo::create([
            'alumno_id' => $alumno->id,
            'concepto' => 'Colegiatura',
            'monto' => 1000,
            'monto_pagado' => 0,
            'estatus' => Adeudo::ESTATUS_PENDIENTE,
            'estatus_id' => Estatus::ACTIVO,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('adeudos.export.excel', ['estatus' => Adeudo::ESTATUS_PENDIENTE]));

        $response->assertOk();
        $response->assertHeader(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
    }
}
