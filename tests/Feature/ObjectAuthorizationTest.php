<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AcademicDocument;
use App\Models\Adeudo;
use App\Models\AdeudoAbono;
use App\Models\Alumno;
use App\Models\Documento;
use App\Models\Estatus;
use App\Models\Pago;
use App\Models\Taller;
use App\Models\TallerAlumno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ObjectAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_documento_download_and_delete_require_active_alumno(): void
    {
        $alumno = Alumno::factory()->create();
        $documento = Documento::create([
            'alumno_id' => $alumno->id,
            'tipo' => 'curp',
            'archivo' => 'documentos/test.pdf',
        ]);

        $alumno->update(['estatus_id' => Estatus::ELIMINADO]);

        $this->actingAs($this->admin)
            ->get(route('documentacion.descargar', $documento))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->delete(route('documentacion.destroy', $documento))
            ->assertForbidden();
    }

    public function test_academic_document_edit_update_delete_require_active_alumno(): void
    {
        $alumno = Alumno::factory()->create();
        $document = AcademicDocument::factory()->asText('Original content')->create([
            'alumno_id' => $alumno->id,
        ]);

        $alumno->update(['estatus_id' => Estatus::INACTIVO]);

        $this->actingAs($this->admin)
            ->get(route('academic-documents.edit', $document))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->put(route('academic-documents.update', $document), [
                'title' => 'New title',
                'content' => 'New content',
            ])
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->delete(route('academic-documents.destroy', $document))
            ->assertForbidden();
    }

    public function test_pago_show_edit_update_delete_require_active_alumno(): void
    {
        $alumno = Alumno::factory()->create();
        $pago = Pago::create([
            'alumno_id' => $alumno->id,
            'mes' => '2026-01',
            'fecha' => now(),
        ]);

        $alumno->update(['estatus_id' => Estatus::ELIMINADO]);

        $this->actingAs($this->admin)
            ->get(route('pagos.show', $pago))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->get(route('pagos.edit', $pago))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->put(route('pagos.update', $pago), [
                'alumno_id' => $alumno->id,
                'mes' => '2026-02',
            ])
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->delete(route('pagos.destroy', $pago))
            ->assertForbidden();
    }

    public function test_pago_inline_update_ignores_alumno_id_change(): void
    {
        $alumnoA = Alumno::factory()->create();
        $alumnoB = Alumno::factory()->create();
        $pago = Pago::create([
            'alumno_id' => $alumnoA->id,
            'mes' => '2026-01',
        ]);

        $this->actingAs($this->admin)
            ->put(route('pagos.inline-update', $pago), [
                'alumno_id' => $alumnoB->id,
                'mes' => '2026-03',
            ])
            ->assertOk();

        $pago->refresh();
        $this->assertSame($alumnoA->id, $pago->alumno_id);
        $this->assertSame('2026-03', $pago->mes);
    }

    public function test_adeudo_show_update_delete_abonar_require_active_alumno(): void
    {
        $alumno = Alumno::factory()->create();
        $adeudo = Adeudo::create([
            'alumno_id' => $alumno->id,
            'concepto' => 'Colegiatura',
            'monto' => 1000,
            'estatus_id' => Estatus::ACTIVO,
        ]);

        $alumno->update(['estatus_id' => Estatus::ELIMINADO]);

        $this->actingAs($this->admin)
            ->get(route('adeudos.show', $adeudo))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->put(route('adeudos.update', $adeudo), [
                'alumno_id' => $alumno->id,
                'concepto' => 'Otro',
                'created_at' => now()->toDateTimeString(),
            ])
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->delete(route('adeudos.destroy', $adeudo))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->post(route('adeudos.abonar', $adeudo), [
                'monto' => 100,
                'fecha' => now()->toDateString(),
            ])
            ->assertForbidden();
    }

    public function test_abono_of_another_adeudo_is_rejected(): void
    {
        $alumno = Alumno::factory()->create();
        $adeudoUno = Adeudo::create([
            'alumno_id' => $alumno->id,
            'concepto' => 'Uno',
            'monto' => 1000,
            'estatus_id' => Estatus::ACTIVO,
        ]);
        $adeudoDos = Adeudo::create([
            'alumno_id' => $alumno->id,
            'concepto' => 'Dos',
            'monto' => 1000,
            'estatus_id' => Estatus::ACTIVO,
        ]);
        $abonoDeDos = AdeudoAbono::create([
            'adeudo_id' => $adeudoDos->id,
            'monto' => 100,
            'fecha' => now(),
        ]);

        $this->actingAs($this->admin)
            ->put(route('adeudos.abonos.update', [$adeudoUno, $abonoDeDos]), [
                'monto' => 200,
            ])
            ->assertNotFound();
    }

    public function test_taller_alumno_monto_update_requires_active_alumno(): void
    {
        $alumno = Alumno::factory()->create();
        $taller = Taller::create([
            'nombre' => 'Taller de prueba',
            'costo' => 500,
        ]);
        $inscripcion = TallerAlumno::create([
            'taller_id' => $taller->id,
            'alumno_id' => $alumno->id,
        ]);

        $alumno->update(['estatus_id' => Estatus::ELIMINADO]);

        $this->actingAs($this->admin)
            ->put(route('talleres.inscripcion.monto.update', $inscripcion), [
                'monto_pagado' => 100,
            ])
            ->assertForbidden();
    }

    public function test_taller_alumno_monto_update_for_nonexistent_record_returns_not_found(): void
    {
        $this->actingAs($this->admin)
            ->put(route('talleres.inscripcion.monto.update', ['tallerAlumno' => 99999]), [
                'monto_pagado' => 100,
            ])
            ->assertNotFound();
    }
}
