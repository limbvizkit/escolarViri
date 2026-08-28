<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\AlumnoArchivo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AlumnoArchivosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_puede_crear_alumno_con_multiples_archivos(): void
    {
        $user = User::factory()->create();
        $imagen = UploadedFile::fake()->image('foto.jpg');
        $pdf = UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf');

        $response = $this
            ->actingAs($user)
            ->post(route('alumnos.store'), [
                'grado_escolar_id' => Alumno::factory()->make()->grado_escolar_id,
                'nombre' => 'Juan',
                'apellido_paterno' => 'Pérez',
                'archivos' => [$imagen, $pdf],
            ]);

        $response->assertRedirect(route('alumnos.index'));
        $response->assertSessionHas('success');

        $alumno = Alumno::latest('id')->first();
        $this->assertCount(2, $alumno->archivos);
        $this->assertTrue(Storage::disk('public')->exists($alumno->archivos->first()->archivo));
    }

    public function test_al_actualizar_se_preservan_los_archivos_existentes_y_se_agregan_nuevos(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create();

        $archivoOriginal = UploadedFile::fake()->image('original.jpg');
        $rutaOriginal = $archivoOriginal->store('alumnos', 'public');
        $alumno->archivos()->create([
            'archivo' => $rutaOriginal,
            'nombre_original' => 'original.jpg',
        ]);

        $nuevoArchivo = UploadedFile::fake()->create('nuevo.pdf', 100, 'application/pdf');

        $response = $this
            ->actingAs($user)
            ->put(route('alumnos.update', $alumno), [
                'grado_escolar_id' => $alumno->grado_escolar_id,
                'nombre' => $alumno->nombre,
                'apellido_paterno' => $alumno->apellido_paterno,
                'archivos' => [$nuevoArchivo],
            ]);

        $response->assertRedirect(route('alumnos.index'));

        $alumno->refresh();
        $this->assertCount(2, $alumno->archivos);
        $this->assertTrue($alumno->archivos->contains('archivo', $rutaOriginal));
    }

    public function test_eliminar_archivo_borra_registro_y_archivo_de_disco(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create();
        $archivoItem = AlumnoArchivo::factory()->for($alumno)->create();

        Storage::disk('public')->put($archivoItem->archivo, 'contenido');
        $this->assertTrue(Storage::disk('public')->exists($archivoItem->archivo));

        $response = $this
            ->actingAs($user)
            ->delete(route('alumnos.archivos.destroy', [$alumno, $archivoItem]));

        $response->assertRedirect(route('alumnos.edit', $alumno));
        $response->assertSessionHas('success');

        $this->assertModelMissing($archivoItem);
        $this->assertFalse(Storage::disk('public')->exists($archivoItem->archivo));
    }

    public function test_la_vista_show_muestra_miniaturas_de_imagenes_y_modal(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create();
        $imagen = UploadedFile::fake()->image('perfil.jpg');
        $ruta = $imagen->store('alumnos', 'public');

        AlumnoArchivo::create([
            'alumno_id' => $alumno->id,
            'archivo' => $ruta,
            'nombre_original' => 'perfil.jpg',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('alumnos.show', $alumno));

        $response->assertOk();
        $response->assertSee(Storage::url($ruta));
        $response->assertSee('data-bs-toggle="modal"', false);
        $response->assertSee('id="archivoModal"', false);
        $response->assertSee('id="archivoModalImg"', false);
    }

    public function test_la_vista_show_muestra_archivos_no_imagen_con_enlace_nueva_pestania(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create();
        $pdf = UploadedFile::fake()->create('constancia.pdf', 100, 'application/pdf');
        $ruta = $pdf->store('alumnos', 'public');

        AlumnoArchivo::create([
            'alumno_id' => $alumno->id,
            'archivo' => $ruta,
            'nombre_original' => 'constancia.pdf',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('alumnos.show', $alumno));

        $response->assertOk();
        $response->assertSee(Storage::url($ruta));
        $response->assertSee('target="_blank"', false);
        $response->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_la_vista_show_muestra_el_archivo_legacy(): void
    {
        $user = User::factory()->create();
        $pdf = UploadedFile::fake()->create('legacy.pdf', 100, 'application/pdf');
        $ruta = $pdf->store('alumnos', 'public');

        $alumno = Alumno::factory()->create(['archivo' => $ruta]);

        $response = $this
            ->actingAs($user)
            ->get(route('alumnos.show', $alumno));

        $response->assertOk();
        $response->assertSee(Storage::url($ruta));
        $response->assertSee('Histórico');
    }

    public function test_el_campo_singular_archivo_sigue_funcionando(): void
    {
        $user = User::factory()->create();
        $pdf = UploadedFile::fake()->create('unico.pdf', 100, 'application/pdf');

        $response = $this
            ->actingAs($user)
            ->post(route('alumnos.store'), [
                'grado_escolar_id' => Alumno::factory()->make()->grado_escolar_id,
                'nombre' => 'María',
                'apellido_paterno' => 'García',
                'archivo' => $pdf,
            ]);

        $response->assertRedirect(route('alumnos.index'));

        $alumno = Alumno::latest('id')->first();
        $this->assertNotNull($alumno->archivo);
        $this->assertTrue(Storage::disk('public')->exists($alumno->archivo));
    }

    public function test_puede_descargar_un_archivo_normalizado(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create();
        $pdf = UploadedFile::fake()->create('reporte.pdf', 100, 'application/pdf');
        $ruta = $pdf->store('alumnos', 'public');

        $archivoItem = AlumnoArchivo::create([
            'alumno_id' => $alumno->id,
            'archivo' => $ruta,
            'nombre_original' => 'reporte.pdf',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('alumnos.archivos.download', [$alumno, $archivoItem]));

        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=reporte.pdf');
    }
}
