<?php

namespace Tests\Feature;

use App\Models\Adeudo;
use App\Models\Alumno;
use App\Models\Estatus;
use App\Models\GradoEscolar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdeudoEditarDetalleTest extends TestCase
{
    use RefreshDatabase;

    private ?GradoEscolar $grado = null;

    private function grado(): GradoEscolar
    {
        if ($this->grado === null) {
            $this->grado = GradoEscolar::create(['nombre' => 'Primaria', 'slug' => 'primaria']);
        }

        return $this->grado;
    }

    private function crearAlumno(array $sobrescribir = []): Alumno
    {
        return Alumno::create(array_merge([
            'grado_escolar_id' => $this->grado()->id,
            'nombre' => 'Ana',
            'apellido_paterno' => 'Garcia',
        ], $sobrescribir));
    }

    private function crearAdeudo(array $sobrescribir = []): Adeudo
    {
        $alumno = $this->crearAlumno();

        return Adeudo::create(array_merge([
            'alumno_id' => $alumno->id,
            'concepto' => 'Colegiatura',
            'anotaciones' => 'Nota inicial',
            'monto' => 1000,
            'monto_pagado' => 0,
            'estatus' => Adeudo::ESTATUS_PENDIENTE,
            'estatus_id' => Estatus::ACTIVO,
            'created_at' => '2026-01-15 10:00:00',
        ], $sobrescribir));
    }

    public function test_muestra_boton_y_formulario_para_editar_detalle(): void
    {
        $adeudo = $this->crearAdeudo();
        $otroAlumno = $this->crearAlumno([
            'nombre' => 'Luis',
            'apellido_paterno' => 'Pérez',
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('adeudos.show', $adeudo));

        $response->assertOk();
        $response->assertSee('Editar detalle');
        $response->assertSeeHtml('id="detalle-form"');
        $response->assertSeeHtml('name="alumno_id"');
        $response->assertSeeHtml('name="concepto"');
        $response->assertSeeHtml('name="anotaciones"');
        $response->assertSeeHtml('name="created_at"');
        $response->assertSee($otroAlumno->nombre_completo);
    }

    public function test_puede_actualizar_los_cuatro_campos_del_detalle(): void
    {
        $adeudo = $this->crearAdeudo();
        $nuevoAlumno = $this->crearAlumno([
            'nombre' => 'Luis',
            'apellido_paterno' => 'Pérez',
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->put(route('adeudos.update', $adeudo), [
                'alumno_id' => $nuevoAlumno->id,
                'concepto' => 'Inscripción',
                'anotaciones' => 'Nota actualizada',
                'created_at' => '2025-12-01',
            ]);

        $response->assertRedirect(route('adeudos.show', $adeudo));
        $response->assertSessionHas('success', 'Adeudo actualizado correctamente.');

        $this->assertDatabaseHas('adeudos', [
            'id' => $adeudo->id,
            'alumno_id' => $nuevoAlumno->id,
            'concepto' => 'Inscripción',
            'anotaciones' => 'Nota actualizada',
        ]);

        $adeudo->refresh();
        $this->assertSame('2025-12-01', $adeudo->created_at->format('Y-m-d'));
    }

    public function test_no_modifica_monto_ni_estatus_ni_monto_pagado(): void
    {
        $adeudo = $this->crearAdeudo([
            'monto' => 5000,
            'monto_pagado' => 2500,
            'estatus' => Adeudo::ESTATUS_PARCIAL,
        ]);
        $nuevoAlumno = $this->crearAlumno([
            'nombre' => 'Luis',
            'apellido_paterno' => 'Pérez',
        ]);

        $this
            ->actingAs(User::factory()->create())
            ->put(route('adeudos.update', $adeudo), [
                'alumno_id' => $nuevoAlumno->id,
                'concepto' => 'Otro concepto',
                'anotaciones' => null,
                'created_at' => '2025-06-15',
            ]);

        $this->assertDatabaseHas('adeudos', [
            'id' => $adeudo->id,
            'monto' => '5000.00',
            'monto_pagado' => '2500.00',
            'estatus' => Adeudo::ESTATUS_PARCIAL,
        ]);
    }

    public function test_valida_campos_requeridos_del_detalle(): void
    {
        $adeudo = $this->crearAdeudo();

        $response = $this
            ->actingAs(User::factory()->create())
            ->from(route('adeudos.show', $adeudo))
            ->put(route('adeudos.update', $adeudo), [
                'alumno_id' => '',
                'concepto' => '',
                'anotaciones' => str_repeat('a', 1001),
                'created_at' => '',
            ]);

        $response->assertRedirect(route('adeudos.show', $adeudo));
        $response->assertSessionHasErrors(['alumno_id', 'concepto', 'anotaciones', 'created_at']);
    }

    public function test_valida_que_el_alumno_exista(): void
    {
        $adeudo = $this->crearAdeudo();

        $response = $this
            ->actingAs(User::factory()->create())
            ->from(route('adeudos.show', $adeudo))
            ->put(route('adeudos.update', $adeudo), [
                'alumno_id' => 99999,
                'concepto' => 'Concepto',
                'anotaciones' => null,
                'created_at' => '2025-06-15',
            ]);

        $response->assertRedirect(route('adeudos.show', $adeudo));
        $response->assertSessionHasErrors(['alumno_id']);
    }

    public function test_mantiene_valores_en_formulario_tras_error_de_validacion(): void
    {
        $adeudo = $this->crearAdeudo();
        $otroAlumno = $this->crearAlumno([
            'nombre' => 'Luis',
            'apellido_paterno' => 'Pérez',
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->from(route('adeudos.show', $adeudo))
            ->put(route('adeudos.update', $adeudo), [
                'alumno_id' => $otroAlumno->id,
                'concepto' => '',
                'anotaciones' => 'Valor enviado',
                'created_at' => '2025-06-15',
            ]);

        $response->assertRedirect(route('adeudos.show', $adeudo));
        $response->assertSessionHasInput([
            'alumno_id' => $otroAlumno->id,
            'anotaciones' => 'Valor enviado',
            'created_at' => '2025-06-15',
        ]);
        $response->assertSessionHasErrors(['concepto']);
    }
}
