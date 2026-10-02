<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AcademicDocument;
use App\Models\Alumno;
use App\Models\AlumnoArchivo;
use App\Models\Documento;
use App\Models\GradoEscolar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class PrivateDocumentStorageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('documents');
        Storage::fake('public');
    }

    private function grado(): GradoEscolar
    {
        return GradoEscolar::create(['nombre' => 'Primaria', 'slug' => 'primaria']);
    }

    public function test_alumno_single_file_is_stored_on_documents_disk(): void
    {
        $pdf = UploadedFile::fake()->create('unico.pdf', 100, 'application/pdf');

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('alumnos.store'), [
                'grado_escolar_id' => $this->grado()->id,
                'nombre' => 'Juan',
                'apellido_paterno' => 'Pérez',
                'sexo' => Alumno::SEXO_NINO,
                'archivo' => $pdf,
            ])
            ->assertRedirect(route('alumnos.index'));

        $alumno = Alumno::latest('id')->first();
        $this->assertNotNull($alumno->archivo);
        $this->assertTrue(Storage::disk('documents')->exists($alumno->archivo));
        $this->assertFalse(Storage::disk('public')->exists($alumno->archivo));
    }

    public function test_alumno_multiple_files_are_stored_on_documents_disk(): void
    {
        $imagen = UploadedFile::fake()->image('foto.jpg');
        $pdf = UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf');

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('alumnos.store'), [
                'grado_escolar_id' => $this->grado()->id,
                'nombre' => 'Ana',
                'apellido_paterno' => 'García',
                'sexo' => Alumno::SEXO_NINA,
                'archivos' => [$imagen, $pdf],
            ])
            ->assertRedirect(route('alumnos.index'));

        $alumno = Alumno::latest('id')->first();
        $this->assertCount(2, $alumno->archivos);

        foreach ($alumno->archivos as $archivo) {
            $this->assertTrue(Storage::disk('documents')->exists($archivo->archivo));
            $this->assertFalse(Storage::disk('public')->exists($archivo->archivo));
        }
    }

    public function test_documentacion_file_is_stored_on_documents_disk(): void
    {
        $alumno = Alumno::factory()->create();
        $pdf = UploadedFile::fake()->create('curp.pdf', 100, 'application/pdf');

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('documentacion.store', $alumno), [
                'documentos' => ['curp' => $pdf],
            ])
            ->assertRedirect(route('documentacion.show', $alumno));

        $documento = Documento::where('alumno_id', $alumno->id)->first();
        $this->assertNotNull($documento);
        $this->assertTrue(Storage::disk('documents')->exists($documento->archivo));
        $this->assertFalse(Storage::disk('public')->exists($documento->archivo));
    }

    public function test_academic_document_file_is_stored_on_documents_disk(): void
    {
        $alumno = Alumno::factory()->create();
        $pdf = UploadedFile::fake()->create('reporte.pdf', 100, 'application/pdf');

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('academic-documents.store', $alumno), [
                'title' => 'Reporte escolar',
                'file' => $pdf,
            ])
            ->assertRedirect(route('academic-documents.show', $alumno));

        $document = AcademicDocument::where('alumno_id', $alumno->id)->first();
        $this->assertNotNull($document);
        $this->assertTrue(Storage::disk('documents')->exists($document->file));
        $this->assertFalse(Storage::disk('public')->exists($document->file));
    }

    public function test_authorized_role_can_download_alumno_archivo(): void
    {
        $alumno = Alumno::factory()->create();
        Storage::disk('documents')->put('alumnos/reporte.pdf', 'contenido');

        $archivoItem = AlumnoArchivo::create([
            'alumno_id' => $alumno->id,
            'archivo' => 'alumnos/reporte.pdf',
            'nombre_original' => 'reporte.pdf',
        ]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('alumnos.archivos.download', [$alumno, $archivoItem]))
            ->assertOk()
            ->assertDownload('reporte.pdf');
    }

    public function test_unauthorized_role_receives_forbidden_on_download(): void
    {
        $alumno = Alumno::factory()->create();
        Storage::disk('documents')->put('alumnos/reporte.pdf', 'contenido');

        $archivoItem = AlumnoArchivo::create([
            'alumno_id' => $alumno->id,
            'archivo' => 'alumnos/reporte.pdf',
            'nombre_original' => 'reporte.pdf',
        ]);

        $this->actingAs(User::factory()->profesor()->create())
            ->get(route('alumnos.archivos.download', [$alumno, $archivoItem]))
            ->assertForbidden();
    }

    public function test_downloading_another_alumnos_archivo_returns_not_found(): void
    {
        $alumnoA = Alumno::factory()->create();
        $alumnoB = Alumno::factory()->create();

        $archivoDeB = AlumnoArchivo::create([
            'alumno_id' => $alumnoB->id,
            'archivo' => 'alumnos/ajeno.pdf',
            'nombre_original' => 'ajeno.pdf',
        ]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('alumnos.archivos.download', [$alumnoA, $archivoDeB]))
            ->assertNotFound();
    }

    public function test_deleting_archivo_removes_it_from_documents_disk(): void
    {
        $alumno = Alumno::factory()->create();
        Storage::disk('documents')->put('alumnos/borrar.pdf', 'contenido');

        $archivoItem = AlumnoArchivo::create([
            'alumno_id' => $alumno->id,
            'archivo' => 'alumnos/borrar.pdf',
            'nombre_original' => 'borrar.pdf',
        ]);

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('alumnos.archivos.destroy', [$alumno, $archivoItem]))
            ->assertRedirect(route('alumnos.edit', $alumno));

        $this->assertModelMissing($archivoItem);
        $this->assertFalse(Storage::disk('documents')->exists('alumnos/borrar.pdf'));
    }

    public function test_legacy_archivo_download_serves_private_file(): void
    {
        $alumno = Alumno::factory()->create(['archivo' => 'alumnos/legacy.pdf']);
        Storage::disk('documents')->put('alumnos/legacy.pdf', 'contenido');

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('alumnos.archivo.download', $alumno))
            ->assertOk()
            ->assertDownload('legacy.pdf');
    }

    public function test_legacy_archivo_download_requires_the_alumno_to_have_a_file(): void
    {
        $alumno = Alumno::factory()->create(['archivo' => null]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('alumnos.archivo.download', $alumno))
            ->assertNotFound();
    }

    public function test_migration_command_moves_public_files_and_is_idempotent(): void
    {
        $alumno = Alumno::factory()->create(['archivo' => 'alumnos/legacy.pdf']);
        Storage::disk('public')->put('alumnos/legacy.pdf', 'contenido');

        $this->artisan('documents:migrate-to-private')->assertExitCode(0);

        $this->assertTrue(Storage::disk('documents')->exists('alumnos/legacy.pdf'));
        $this->assertFalse(Storage::disk('public')->exists('alumnos/legacy.pdf'));

        // Running it again must not fail nor duplicate anything.
        $this->artisan('documents:migrate-to-private')->assertExitCode(0);

        $this->assertTrue(Storage::disk('documents')->exists('alumnos/legacy.pdf'));
        $this->assertSame('contenido', Storage::disk('documents')->get('alumnos/legacy.pdf'));
    }

    public function test_migration_command_dry_run_does_not_move_files(): void
    {
        $alumno = Alumno::factory()->create(['archivo' => 'alumnos/dry.pdf']);
        Storage::disk('public')->put('alumnos/dry.pdf', 'contenido');

        $this->artisan('documents:migrate-to-private', ['--dry-run' => true])->assertExitCode(0);

        $this->assertTrue(Storage::disk('public')->exists('alumnos/dry.pdf'));
        $this->assertFalse(Storage::disk('documents')->exists('alumnos/dry.pdf'));
    }

    public function test_downloading_archivo_missing_on_both_disks_returns_not_found(): void
    {
        $alumno = Alumno::factory()->create();

        $archivoItem = AlumnoArchivo::create([
            'alumno_id' => $alumno->id,
            'archivo' => 'alumnos/faltante.pdf',
            'nombre_original' => 'faltante.pdf',
        ]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('alumnos.archivos.download', [$alumno, $archivoItem]))
            ->assertNotFound();
    }

    public function test_downloading_archivo_falls_back_to_public_disk(): void
    {
        $alumno = Alumno::factory()->create();
        Storage::disk('public')->put('alumnos/legacy-public.pdf', 'contenido publico');

        $archivoItem = AlumnoArchivo::create([
            'alumno_id' => $alumno->id,
            'archivo' => 'alumnos/legacy-public.pdf',
            'nombre_original' => 'legacy-public.pdf',
        ]);

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('alumnos.archivos.download', [$alumno, $archivoItem]));

        $response->assertOk()->assertDownload('legacy-public.pdf');
        $this->assertSame('contenido publico', $response->streamedContent());
    }

    public function test_migration_command_moves_orphan_files_under_sensitive_prefixes(): void
    {
        Storage::disk('public')->put('alumnos/orphan.pdf', 'alumno');
        Storage::disk('public')->put('documentos/1/orphan.png', 'documento');
        Storage::disk('public')->put('academic-documents/1/orphan.pdf', 'academico');
        Storage::disk('public')->put('.gitignore', 'ignored');
        Storage::disk('public')->put('avatars/user.png', 'unrelated');

        $this->artisan('documents:migrate-to-private')->assertExitCode(0);

        $this->assertTrue(Storage::disk('documents')->exists('alumnos/orphan.pdf'));
        $this->assertTrue(Storage::disk('documents')->exists('documentos/1/orphan.png'));
        $this->assertTrue(Storage::disk('documents')->exists('academic-documents/1/orphan.pdf'));

        $this->assertFalse(Storage::disk('public')->exists('alumnos/orphan.pdf'));
        $this->assertFalse(Storage::disk('public')->exists('documentos/1/orphan.png'));
        $this->assertFalse(Storage::disk('public')->exists('academic-documents/1/orphan.pdf'));

        $this->assertTrue(Storage::disk('public')->exists('.gitignore'));
        $this->assertTrue(Storage::disk('public')->exists('avatars/user.png'));
    }

    public function test_migration_command_orphan_sweep_is_idempotent_and_dry_run_safe(): void
    {
        Storage::disk('public')->put('documentos/1/orphan.pdf', 'contenido');

        $this->artisan('documents:migrate-to-private', ['--dry-run' => true])->assertExitCode(0);

        $this->assertTrue(Storage::disk('public')->exists('documentos/1/orphan.pdf'));
        $this->assertFalse(Storage::disk('documents')->exists('documentos/1/orphan.pdf'));

        $this->artisan('documents:migrate-to-private')->assertExitCode(0);

        $this->assertTrue(Storage::disk('documents')->exists('documentos/1/orphan.pdf'));
        $this->assertFalse(Storage::disk('public')->exists('documentos/1/orphan.pdf'));

        $this->artisan('documents:migrate-to-private')->assertExitCode(0);

        $this->assertSame('contenido', Storage::disk('documents')->get('documentos/1/orphan.pdf'));
    }

    public function test_archivo_download_works_from_documents_after_migration(): void
    {
        $alumno = Alumno::factory()->create(['archivo' => 'alumnos/legacy.pdf']);
        Storage::disk('public')->put('alumnos/legacy.pdf', 'contenido migrado');

        $this->artisan('documents:migrate-to-private')->assertExitCode(0);

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('alumnos.archivo.download', $alumno));

        $response->assertOk()->assertDownload('legacy.pdf');
        $this->assertSame('contenido migrado', $response->streamedContent());
    }
}
