<?php

namespace Tests\Feature;

use App\Models\Adeudo;
use App\Models\Alumno;
use App\Models\Estatus;
use App\Models\GradoEscolar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Tests\TestCase;

class AdeudoExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_los_links_de_exportacion_incluyen_los_filtros_activos(): void
    {
        $user = User::factory()->admin()->create();
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
        $user = User::factory()->admin()->create();
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
        $user = User::factory()->admin()->create();
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

    public function test_el_export_de_excel_incluye_las_columnas_mes_y_anio(): void
    {
        $user = User::factory()->admin()->create();
        $grado = GradoEscolar::factory()->create();
        $alumno = Alumno::factory()->create(['grado_escolar_id' => $grado->id]);
        Adeudo::create([
            'alumno_id' => $alumno->id,
            'concepto' => 'Colegiatura',
            'mes' => 3,
            'anio' => 2026,
            'monto' => 1000,
            'monto_pagado' => 0,
            'estatus' => Adeudo::ESTATUS_PENDIENTE,
            'estatus_id' => Estatus::ACTIVO,
        ]);

        $rows = $this->exportedExcelRows(
            $this->actingAs($user)->get(route('adeudos.export.excel'))
        );

        $this->assertSame(
            ['#', 'Alumno', 'Concepto', 'Mes', 'Año', 'Monto', 'Abonado', 'Pendiente', 'Estatus'],
            $rows[0]
        );
        $this->assertSame('Marzo', $rows[1][3]);
        $this->assertSame('2026', $rows[1][4]);
    }

    public function test_el_export_de_excel_respeta_los_filtros_de_mes_y_anio(): void
    {
        $user = User::factory()->admin()->create();
        $grado = GradoEscolar::factory()->create();
        $alumno = Alumno::factory()->create(['grado_escolar_id' => $grado->id]);

        foreach ([[1, 2026, 'Colegiatura enero'], [2, 2026, 'Colegiatura febrero'], [1, 2025, 'Colegiatura enero 2025']] as [$mes, $anio, $concepto]) {
            Adeudo::create([
                'alumno_id' => $alumno->id,
                'concepto' => $concepto,
                'mes' => $mes,
                'anio' => $anio,
                'monto' => 1000,
                'monto_pagado' => 0,
                'estatus' => Adeudo::ESTATUS_PENDIENTE,
                'estatus_id' => Estatus::ACTIVO,
            ]);
        }

        $rows = $this->exportedExcelRows(
            $this->actingAs($user)->get(route('adeudos.export.excel', ['mes' => 1, 'anio' => 2026]))
        );

        $dataRows = array_slice($rows, 1);

        $this->assertCount(1, $dataRows);
        $this->assertSame('Colegiatura enero', $dataRows[0][2]);
    }

    public function test_el_export_de_pdf_respeta_los_filtros_de_mes_y_anio(): void
    {
        $user = User::factory()->admin()->create();
        $grado = GradoEscolar::factory()->create();
        $alumno = Alumno::factory()->create(['grado_escolar_id' => $grado->id]);

        foreach ([[1, 2026, 'Colegiatura enero'], [2, 2026, 'Colegiatura febrero']] as [$mes, $anio, $concepto]) {
            Adeudo::create([
                'alumno_id' => $alumno->id,
                'concepto' => $concepto,
                'mes' => $mes,
                'anio' => $anio,
                'monto' => 1000,
                'monto_pagado' => 0,
                'estatus' => Adeudo::ESTATUS_PENDIENTE,
                'estatus_id' => Estatus::ACTIVO,
            ]);
        }

        $response = $this
            ->actingAs($user)
            ->get(route('adeudos.export.pdf', ['mes' => 1, 'anio' => 2026]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_el_export_de_excel_incluye_el_logo_y_el_nombre_de_la_escuela_en_el_encabezado(): void
    {
        $user = User::factory()->admin()->create();

        $sheet = $this->exportedExcelSheet(
            $this->actingAs($user)->get(route('adeudos.export.excel'))
        );

        $this->assertSame('Learning Play House', $sheet->getCell('C1')->getValue());
        $this->assertTrue($sheet->getStyle('C1')->getFont()->getBold());
        $this->assertSame(16.0, $sheet->getStyle('C1')->getFont()->getSize());
        $this->assertCount(1, $sheet->getDrawingCollection());
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    private function exportedExcelRows(TestResponse $response): array
    {
        return array_slice($this->exportedExcelSheet($response)->toArray(), 2);
    }

    private function exportedExcelSheet(TestResponse $response): Worksheet
    {
        $response->assertOk();

        $path = $response->baseResponse->getFile()->getPathname();

        return IOFactory::load($path)->getActiveSheet();
    }
}
