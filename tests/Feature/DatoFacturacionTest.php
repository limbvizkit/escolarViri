<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\DatoFacturacion;
use App\Models\GradoEscolar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DatoFacturacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('documents');
        Storage::fake('public');
    }

    public function test_invitado_no_puede_acceder_al_modulo(): void
    {
        $response = $this->get(route('datos-facturacion.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_index_solo_muestra_alumnos_con_documentos_cargados(): void
    {
        $user = User::factory()->admin()->create();
        $grado = GradoEscolar::factory()->create(['nombre' => 'Primero de Primaria']);

        $alumnoConDocumento = Alumno::factory()->create([
            'nombre' => 'Carlos',
            'apellido_paterno' => 'Hernandez',
            'grado_escolar_id' => $grado->id,
        ]);

        $alumnoSinDocumento = Alumno::factory()->create([
            'nombre' => 'Mariana',
            'apellido_paterno' => 'Zapata',
            'grado_escolar_id' => $grado->id,
        ]);

        DatoFacturacion::create([
            'alumno_id' => $alumnoConDocumento->id,
            'archivo' => 'datos-facturacion/'.$alumnoConDocumento->id.'/recibo.pdf',
            'nombre_original' => 'recibo-colegiatura.pdf',
            'mime_type' => 'application/pdf',
            'mensaje' => 'Factura mes de Octubre',
        ]);

        $response = $this->actingAs($user)->get(route('datos-facturacion.index'));

        $response->assertOk();
        $response->assertSee('Carlos Hernandez');
        $response->assertSee('recibo-colegiatura.pdf');
        $response->assertSee('Factura mes de Octubre');
        // Debe mostrar la fecha y hora de guardado
        $response->assertSee(now()->format('d/m/Y H:i'));
        // El alumno sin documentos NO debe aparecer en la tabla
        $response->assertDontSee('Mariana Zapata');
    }

    public function test_puede_subir_multiples_recibos_con_mensaje_para_un_alumno(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();

        $archivo1 = UploadedFile::fake()->create('factura_A1.pdf', 150, 'application/pdf');
        $archivo2 = UploadedFile::fake()->create('factura_A1.xml', 80, 'text/xml');

        $response = $this->actingAs($user)->post(route('datos-facturacion.store'), [
            'alumno_id' => $alumno->id,
            'documentos' => [$archivo1, $archivo2],
            'mensaje' => 'Comprobantes fiscales folio 1234',
        ]);

        $response->assertRedirect(route('datos-facturacion.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('datos_facturacion', 2);

        $this->assertDatabaseHas('datos_facturacion', [
            'alumno_id' => $alumno->id,
            'nombre_original' => 'factura_A1.pdf',
            'mensaje' => 'Comprobantes fiscales folio 1234',
        ]);

        $this->assertDatabaseHas('datos_facturacion', [
            'alumno_id' => $alumno->id,
            'nombre_original' => 'factura_A1.xml',
            'mensaje' => 'Comprobantes fiscales folio 1234',
        ]);

        $doc = DatoFacturacion::first();
        Storage::disk('documents')->assertExists($doc->archivo);
    }

    public function test_puede_descargar_recibo_de_facturacion(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();

        $path = 'datos-facturacion/'.$alumno->id.'/recibo.pdf';
        Storage::disk('documents')->put($path, 'contenido-ficticio-del-pdf');

        $doc = DatoFacturacion::create([
            'alumno_id' => $alumno->id,
            'archivo' => $path,
            'nombre_original' => 'recibo-100.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($user)->get(route('datos-facturacion.descargar', $doc));

        $response->assertOk();
    }

    public function test_puede_eliminar_recibo_de_facturacion(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();

        $path = 'datos-facturacion/'.$alumno->id.'/recibo.pdf';
        Storage::disk('documents')->put($path, 'contenido-pdf');

        $doc = DatoFacturacion::create([
            'alumno_id' => $alumno->id,
            'archivo' => $path,
            'nombre_original' => 'recibo-100.pdf',
        ]);

        $response = $this->actingAs($user)->delete(route('datos-facturacion.destroy', $doc));

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('datos_facturacion', ['id' => $doc->id]);
        Storage::disk('documents')->assertMissing($path);
    }

    public function test_vista_show_muestra_todos_los_recibos_del_alumno(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create(['nombre' => 'Estudiante', 'apellido_paterno' => 'Prueba']);

        DatoFacturacion::create([
            'alumno_id' => $alumno->id,
            'archivo' => 'ruta/archivo1.pdf',
            'nombre_original' => 'comprobante1.pdf',
            'mensaje' => 'Primer recibo',
        ]);

        DatoFacturacion::create([
            'alumno_id' => $alumno->id,
            'archivo' => 'ruta/archivo2.xml',
            'nombre_original' => 'comprobante2.xml',
            'mensaje' => 'Segundo recibo',
        ]);

        $response = $this->actingAs($user)->get(route('datos-facturacion.show', $alumno));

        $response->assertOk();
        $response->assertSee('Estudiante Prueba');
        $response->assertSee('comprobante1.pdf');
        $response->assertSee('comprobante2.xml');
        $response->assertSee('Primer recibo');
        $response->assertSee('Segundo recibo');
    }
}
