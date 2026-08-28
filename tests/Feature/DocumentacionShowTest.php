<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Documento;
use App\Models\GradoEscolar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentacionShowTest extends TestCase
{
    use RefreshDatabase;

    private function alumnoConDocumentos(): Alumno
    {
        $grado = GradoEscolar::create(['nombre' => 'Primaria', 'slug' => 'primaria']);

        $alumno = Alumno::create([
            'grado_escolar_id' => $grado->id,
            'nombre' => 'Ana',
            'apellido_paterno' => 'García',
        ]);

        Storage::fake('public');

        $imagen = UploadedFile::fake()->image('acta.jpg');
        $rutaImagen = $imagen->store('documentos/'.$alumno->id, 'public');

        $pdf = UploadedFile::fake()->create('curp.pdf', 100, 'application/pdf');
        $rutaPdf = $pdf->store('documentos/'.$alumno->id, 'public');

        Documento::create([
            'alumno_id' => $alumno->id,
            'tipo' => 'acta_nacimiento',
            'archivo' => $rutaImagen,
        ]);

        Documento::create([
            'alumno_id' => $alumno->id,
            'tipo' => 'curp',
            'archivo' => $rutaPdf,
        ]);

        return $alumno->fresh('documentos');
    }

    public function test_muestra_miniatura_de_imagen_y_modal_de_vista_previa(): void
    {
        $alumno = $this->alumnoConDocumentos();
        $documento = $alumno->documentos->firstWhere('tipo', 'acta_nacimiento');

        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('documentacion.show', $alumno));

        $response->assertOk();
        $response->assertSee(Storage::url($documento->archivo));
        $response->assertSee('data-bs-toggle="modal"', false);
        $response->assertSee('id="documentoModal"', false);
        $response->assertSee('id="documentoModalImg"', false);
    }

    public function test_los_archivos_no_imagen_abren_en_nueva_pestania(): void
    {
        $alumno = $this->alumnoConDocumentos();
        $documento = $alumno->documentos->firstWhere('tipo', 'curp');

        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('documentacion.show', $alumno));

        $response->assertOk();
        $response->assertSee(Storage::url($documento->archivo));
        $response->assertSee('target="_blank"', false);
        $response->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_mantiene_acciones_de_descarga_y_eliminacion(): void
    {
        $alumno = $this->alumnoConDocumentos();
        $documento = $alumno->documentos->firstWhere('tipo', 'acta_nacimiento');

        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('documentacion.show', $alumno));

        $response->assertOk();
        $response->assertSee(route('documentacion.descargar', $documento));
        $response->assertSee(route('documentacion.destroy', $documento));
    }
}
