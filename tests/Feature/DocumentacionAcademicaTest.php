<?php

namespace Tests\Feature;

use App\Models\AcademicDocument;
use App\Models\Alumno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentacionAcademicaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_invitado_no_puede_acceder_al_index(): void
    {
        $response = $this->get(route('academic-documents.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_lista_alumnos_con_conteo_de_documentos_academicos(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create();
        AcademicDocument::factory()->for($alumno)->count(2)->create();

        $response = $this
            ->actingAs($user)
            ->get(route('academic-documents.index'));

        $response->assertOk();
        $response->assertSee($alumno->nombre_completo);
        $response->assertSee('2');
        $response->assertSee(route('academic-documents.show', $alumno));
    }

    public function test_puede_crear_documento_de_texto(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('academic-documents.store', $alumno), [
                'title' => 'Notas de seguimiento',
                'description' => 'Observaciones del trimestre',
                'content' => 'El alumno muestra buen progreso en lectura.',
            ]);

        $response->assertRedirect(route('academic-documents.show', $alumno));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('academic_documents', [
            'alumno_id' => $alumno->id,
            'title' => 'Notas de seguimiento',
            'content' => 'El alumno muestra buen progreso en lectura.',
            'file' => null,
        ]);
    }

    public function test_puede_crear_documento_con_archivo(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create();
        $pdf = UploadedFile::fake()->create('reporte.pdf', 100, 'application/pdf');

        $response = $this
            ->actingAs($user)
            ->post(route('academic-documents.store', $alumno), [
                'title' => 'Reporte escolar',
                'description' => 'Reporte del ciclo escolar',
                'file' => $pdf,
            ]);

        $response->assertRedirect(route('academic-documents.show', $alumno));
        $response->assertSessionHas('success');

        $document = AcademicDocument::where('alumno_id', $alumno->id)->first();
        $this->assertNotNull($document);
        $this->assertTrue($document->isFile());
        $this->assertTrue(Storage::disk('public')->exists($document->file));
        $this->assertSame('reporte.pdf', $document->original_name);
        $this->assertNull($document->content);
    }

    public function test_la_vista_show_muestra_miniatura_de_imagen_y_enlace_en_nueva_pestania(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create();
        $imagen = UploadedFile::fake()->image('trabajo.jpg');
        $ruta = $imagen->store('academic-documents/'.$alumno->id, 'public');

        $document = AcademicDocument::create([
            'alumno_id' => $alumno->id,
            'title' => 'Trabajo escolar',
            'file' => $ruta,
            'original_name' => 'trabajo.jpg',
            'mime_type' => 'image/jpeg',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('academic-documents.show', $alumno));

        $response->assertOk();
        $response->assertSee($document->title);
        $response->assertSee(Storage::url($ruta));
        $response->assertSee('target="_blank"', false);
        $response->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_la_vista_show_muestra_archivo_no_imagen_con_enlace_nueva_pestania(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create();
        $pdf = UploadedFile::fake()->create('constancia.pdf', 100, 'application/pdf');
        $ruta = $pdf->store('academic-documents/'.$alumno->id, 'public');

        AcademicDocument::create([
            'alumno_id' => $alumno->id,
            'title' => 'Constancia',
            'file' => $ruta,
            'original_name' => 'constancia.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('academic-documents.show', $alumno));

        $response->assertOk();
        $response->assertSee(Storage::url($ruta));
        $response->assertSee('target="_blank"', false);
        $response->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_puede_editar_metadatos_de_documento_de_texto(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create();
        $document = AcademicDocument::factory()->for($alumno)->asText('Contenido original')->create();

        $response = $this
            ->actingAs($user)
            ->put(route('academic-documents.update', $document), [
                'title' => 'Notas actualizadas',
                'description' => 'Nueva descripción',
                'content' => 'Contenido actualizado',
            ]);

        $response->assertRedirect(route('academic-documents.show', $alumno));
        $response->assertSessionHas('success');

        $document->refresh();
        $this->assertSame('Notas actualizadas', $document->title);
        $this->assertSame('Contenido actualizado', $document->content);
        $this->assertFalse($document->isFile());
    }

    public function test_puede_reemplazar_archivo_en_documento_existente(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create();
        $original = UploadedFile::fake()->create('original.pdf', 100, 'application/pdf');
        $rutaOriginal = $original->store('academic-documents/'.$alumno->id, 'public');

        $document = AcademicDocument::create([
            'alumno_id' => $alumno->id,
            'title' => 'Documento original',
            'file' => $rutaOriginal,
            'original_name' => 'original.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $nuevo = UploadedFile::fake()->image('nuevo.jpg');

        $response = $this
            ->actingAs($user)
            ->put(route('academic-documents.update', $document), [
                'title' => 'Documento reemplazado',
                'file' => $nuevo,
            ]);

        $response->assertRedirect(route('academic-documents.show', $alumno));

        $document->refresh();
        $this->assertTrue($document->isFile());
        $this->assertSame('nuevo.jpg', $document->original_name);
        $this->assertFalse(Storage::disk('public')->exists($rutaOriginal));
        $this->assertTrue(Storage::disk('public')->exists($document->file));
    }

    public function test_eliminar_documento_borra_registro_y_archivo_de_disco(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create();
        $pdf = UploadedFile::fake()->create('borrar.pdf', 100, 'application/pdf');
        $ruta = $pdf->store('academic-documents/'.$alumno->id, 'public');

        $document = AcademicDocument::create([
            'alumno_id' => $alumno->id,
            'title' => 'Documento a borrar',
            'file' => $ruta,
            'original_name' => 'borrar.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $this->assertTrue(Storage::disk('public')->exists($ruta));

        $response = $this
            ->actingAs($user)
            ->delete(route('academic-documents.destroy', $document));

        $response->assertRedirect(route('academic-documents.show', $alumno));
        $response->assertSessionHas('success');

        $this->assertModelMissing($document);
        $this->assertFalse(Storage::disk('public')->exists($ruta));
    }

    public function test_valida_titulo_obligatorio(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('academic-documents.store', $alumno), [
                'title' => '',
                'content' => 'Contenido sin título',
            ]);

        $response->assertSessionHasErrors('title');
        $this->assertDatabaseCount('academic_documents', 0);
    }

    public function test_valida_que_debe_haber_contenido_o_archivo(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('academic-documents.store', $alumno), [
                'title' => 'Solo metadatos',
                'description' => '',
                'content' => '',
            ]);

        $response->assertSessionHasErrors('content');
        $this->assertDatabaseCount('academic_documents', 0);
    }

    public function test_valida_tipo_de_archivo_permitido(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create();
        $archivo = UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload');

        $response = $this
            ->actingAs($user)
            ->post(route('academic-documents.store', $alumno), [
                'title' => 'Archivo inválido',
                'file' => $archivo,
            ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseCount('academic_documents', 0);
    }

    public function test_la_busqueda_filtra_alumnos(): void
    {
        $user = User::factory()->create();
        $buscado = Alumno::factory()->create([
            'nombre' => 'Zara',
            'apellido_paterno' => 'López',
        ]);
        Alumno::factory()->create([
            'nombre' => 'Pedro',
            'apellido_paterno' => 'Gómez',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('academic-documents.index', ['q' => 'Zara']));

        $response->assertOk();
        $response->assertSee($buscado->nombre_completo);
        $response->assertDontSee('Pedro');
    }
}
