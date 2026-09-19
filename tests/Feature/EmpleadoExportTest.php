<?php

namespace Tests\Feature;

use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpleadoExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_los_links_de_exportacion_incluyen_los_filtros_activos(): void
    {
        $user = User::factory()->create();
        $sucursal = Sucursal::factory()->create();
        Empleado::factory()->create([
            'sucursal_id' => $sucursal->id,
            'nombre' => 'Zafiro',
            'apellido_paterno' => 'Zúñiga',
            'estatus_id' => Estatus::ACTIVO,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('empleados.index', [
                'sucursal_id' => $sucursal->id,
                'estatus' => Estatus::ACTIVO,
                'q' => 'Zafiro',
            ]));

        $response->assertOk();

        $html = $response->getContent();

        $this->assertStringContainsString(
            str_replace('&', '&amp;', route('empleados.export.pdf', ['q' => 'Zafiro', 'estatus' => Estatus::ACTIVO, 'sucursal_id' => $sucursal->id])),
            $html
        );
        $this->assertStringContainsString(
            str_replace('&', '&amp;', route('empleados.export.excel', ['q' => 'Zafiro', 'estatus' => Estatus::ACTIVO, 'sucursal_id' => $sucursal->id])),
            $html
        );
    }

    public function test_puede_descargar_pdf_de_empleados_filtrado(): void
    {
        $user = User::factory()->create();
        Empleado::factory()->create(['estatus_id' => Estatus::ACTIVO]);

        $response = $this
            ->actingAs($user)
            ->get(route('empleados.export.pdf', ['estatus' => Estatus::ACTIVO]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_puede_descargar_excel_de_empleados_filtrado(): void
    {
        $user = User::factory()->create();
        Empleado::factory()->create(['estatus_id' => Estatus::ACTIVO]);

        $response = $this
            ->actingAs($user)
            ->get(route('empleados.export.excel', ['estatus' => Estatus::ACTIVO]));

        $response->assertOk();
        $response->assertHeader(
            'Content-Type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
    }
}
