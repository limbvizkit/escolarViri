<?php

namespace Tests\Feature;

use App\Models\Adeudo;
use App\Models\AdeudoAbono;
use App\Models\Alumno;
use App\Models\Estatus;
use App\Models\GradoEscolar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdeudoEliminarTest extends TestCase
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

    private function crearAdeudo(array $sobrescribir = []): Adeudo
    {
        $alumno = Alumno::create([
            'grado_escolar_id' => $this->grado()->id,
            'nombre' => 'Ana',
            'apellido_paterno' => 'Garcia',
        ]);

        return Adeudo::create(array_merge([
            'alumno_id' => $alumno->id,
            'concepto' => 'Colegiatura',
            'monto' => 1000,
            'monto_pagado' => 0,
            'estatus' => Adeudo::ESTATUS_PENDIENTE,
            'estatus_id' => Estatus::ACTIVO,
        ], $sobrescribir));
    }

    public function test_el_listado_no_muestra_adeudos_eliminados(): void
    {
        $activo = $this->crearAdeudo(['concepto' => 'Adeudo activo']);
        $eliminado = $this->crearAdeudo([
            'concepto' => 'Adeudo eliminado',
            'estatus_id' => Estatus::ELIMINADO,
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('adeudos.index'));

        $response->assertOk();
        $response->assertSee('Adeudo activo');
        $response->assertDontSee('Adeudo eliminado');
    }

    public function test_puede_eliminar_logicamente_un_adeudo_sin_abonos(): void
    {
        $adeudo = $this->crearAdeudo();

        $response = $this
            ->actingAs(User::factory()->create())
            ->delete(route('adeudos.destroy', $adeudo));

        $response->assertRedirect(route('adeudos.index'));
        $response->assertSessionHas('success', 'Adeudo eliminado correctamente.');

        $this->assertDatabaseHas('adeudos', [
            'id' => $adeudo->id,
            'estatus_id' => Estatus::ELIMINADO,
        ]);
    }

    public function test_no_puede_eliminar_un_adeudo_con_abonos(): void
    {
        $adeudo = $this->crearAdeudo();
        AdeudoAbono::create([
            'adeudo_id' => $adeudo->id,
            'monto' => 100,
            'fecha' => now()->format('Y-m-d'),
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->delete(route('adeudos.destroy', $adeudo));

        $response->assertRedirect(route('adeudos.index'));
        $response->assertSessionHas('error', 'No es posible eliminar este adeudo porque tiene abonos registrados.');

        $this->assertDatabaseHas('adeudos', [
            'id' => $adeudo->id,
            'estatus_id' => Estatus::ACTIVO,
        ]);
    }

    public function test_el_listado_filtra_por_estatus_de_pago_y_excluye_eliminados(): void
    {
        $pendiente = $this->crearAdeudo([
            'concepto' => 'Pendiente activo',
            'estatus' => Adeudo::ESTATUS_PENDIENTE,
        ]);
        $pagado = $this->crearAdeudo([
            'concepto' => 'Pagado activo',
            'estatus' => Adeudo::ESTATUS_PAGADO,
            'monto_pagado' => 1000,
        ]);
        $eliminadoPendiente = $this->crearAdeudo([
            'concepto' => 'Pendiente eliminado',
            'estatus' => Adeudo::ESTATUS_PENDIENTE,
            'estatus_id' => Estatus::ELIMINADO,
        ]);

        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('adeudos.index', ['estatus' => Adeudo::ESTATUS_PENDIENTE]));

        $response->assertOk();
        $response->assertSee('Pendiente activo');
        $response->assertDontSee('Pagado activo');
        $response->assertDontSee('Pendiente eliminado');
    }
}
