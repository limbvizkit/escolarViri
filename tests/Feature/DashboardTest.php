<?php

namespace Tests\Feature;

use App\Models\Adeudo;
use App\Models\Alumno;
use App\Models\Estatus;
use App\Models\GradoEscolar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_aggregated_adeudos_data(): void
    {
        $user = User::factory()->create();
        $grado = GradoEscolar::factory()->create(['nombre' => '3ro Primaria']);

        $alumnoConMayorSaldo = Alumno::factory()->create([
            'grado_escolar_id' => $grado->id,
            'nombre' => 'Ana',
            'apellido_paterno' => 'García',
            'apellido_materno' => null,
        ]);

        $alumnoConMenorSaldo = Alumno::factory()->create([
            'grado_escolar_id' => $grado->id,
            'nombre' => 'Luis',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => null,
        ]);

        Adeudo::create([
            'alumno_id' => $alumnoConMenorSaldo->id,
            'concepto' => 'Colegiatura',
            'monto' => 1000,
            'monto_pagado' => 0,
            'estatus' => Adeudo::ESTATUS_PENDIENTE,
            'estatus_id' => Estatus::ACTIVO,
        ]);

        Adeudo::create([
            'alumno_id' => $alumnoConMayorSaldo->id,
            'concepto' => 'Inscripción',
            'monto' => 2000,
            'monto_pagado' => 500,
            'estatus' => Adeudo::ESTATUS_PARCIAL,
            'estatus_id' => Estatus::ACTIVO,
        ]);

        Adeudo::create([
            'alumno_id' => $alumnoConMayorSaldo->id,
            'concepto' => 'Material pagado',
            'monto' => 500,
            'monto_pagado' => 500,
            'estatus' => Adeudo::ESTATUS_PAGADO,
            'estatus_id' => Estatus::ACTIVO,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Adeudos pendientes');
        $response->assertSee('3ro Primaria');
        $response->assertSee('Ana García');
        $response->assertSee('Luis Pérez');
        $response->assertSee('2500');
        $response->assertSee('1500');
        $response->assertSee('1000');
    }

    public function test_dashboard_adeudos_chart_has_both_modes(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Por grado escolar');
        $response->assertSee('Por alumno');
        $response->assertSee('chartAdeudosPendientes');
        $response->assertSee('adeudosPorGrado');
        $response->assertSee('adeudosPorAlumno');
    }

    public function test_dashboard_hides_recent_sections(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('Escuelas recientes');
        $response->assertDontSee('Sucursales recientes');
        $response->assertDontSee('Empleados recientes');
        $response->assertDontSee('Alumnos recientes');
    }
}
