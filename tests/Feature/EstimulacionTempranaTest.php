<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\GradoEscolar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstimulacionTempranaTest extends TestCase
{
    use RefreshDatabase;

    private function gradoEt(): GradoEscolar
    {
        return GradoEscolar::create([
            'nombre' => 'ESTIMULACIÓN TEMPRANA',
            'slug' => GradoEscolar::SLUG_ESTIMULACION_TEMPRANA,
        ]);
    }

    private function gradoRegular(): GradoEscolar
    {
        return GradoEscolar::create(['nombre' => 'KINDER 1', 'slug' => 'kinder-1']);
    }

    public function test_el_modulo_et_lista_solo_alumnos_de_estimulacion_temprana(): void
    {
        $user = User::factory()->admin()->create();
        $et = $this->gradoEt();
        $regular = $this->gradoRegular();

        Alumno::factory()->create([
            'grado_escolar_id' => $et->id,
            'nombre' => 'AlumEtUnico',
        ]);
        Alumno::factory()->create([
            'grado_escolar_id' => $regular->id,
            'nombre' => 'AlumRegUnico',
        ]);

        $response = $this->actingAs($user)->get(route('estimulacion-temprana.index'));

        $response->assertOk();
        $response->assertSee('AlumEtUnico');
        $response->assertDontSee('AlumRegUnico');
    }

    public function test_el_modulo_alumnos_ya_no_lista_los_de_estimulacion_temprana(): void
    {
        $user = User::factory()->admin()->create();
        $et = $this->gradoEt();
        $regular = $this->gradoRegular();

        Alumno::factory()->create([
            'grado_escolar_id' => $et->id,
            'nombre' => 'AlumEtUnico',
        ]);
        Alumno::factory()->create([
            'grado_escolar_id' => $regular->id,
            'nombre' => 'AlumRegUnico',
        ]);

        $response = $this->actingAs($user)->get(route('alumnos.index'));

        $response->assertOk();
        $response->assertSee('AlumRegUnico');
        $response->assertDontSee('AlumEtUnico');
    }

    public function test_el_formulario_et_fija_el_grado_escolar(): void
    {
        $user = User::factory()->admin()->create();
        $this->gradoEt();
        $this->gradoRegular();

        $response = $this->actingAs($user)->get(route('estimulacion-temprana.create'));

        $response->assertOk();
        $response->assertSee('ESTIMULACIÓN TEMPRANA');
        $response->assertDontSee('KINDER 1');
    }

    public function test_el_formulario_de_alumnos_ya_no_ofrece_estimulacion_temprana(): void
    {
        $user = User::factory()->admin()->create();
        $this->gradoEt();
        $this->gradoRegular();

        $response = $this->actingAs($user)->get(route('alumnos.create'));

        $response->assertOk();
        $response->assertSee('KINDER 1');
        $response->assertDontSee('ESTIMULACIÓN TEMPRANA');
    }

    public function test_crea_un_alumno_de_estimulacion_temprana(): void
    {
        $user = User::factory()->admin()->create();
        $et = $this->gradoEt();

        $response = $this->actingAs($user)->post(route('estimulacion-temprana.store'), [
            'grado_escolar_id' => $et->id,
            'nombre' => 'Lucía',
            'apellido_paterno' => 'Pérez',
            'sexo' => Alumno::SEXO_NINA,
        ]);

        $response->assertRedirect(route('estimulacion-temprana.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('alumnos', [
            'nombre' => 'Lucía',
            'grado_escolar_id' => $et->id,
        ]);
    }

    public function test_el_modulo_et_rechaza_un_grado_distinto(): void
    {
        $user = User::factory()->admin()->create();
        $this->gradoEt();
        $regular = $this->gradoRegular();

        $response = $this->actingAs($user)->post(route('estimulacion-temprana.store'), [
            'grado_escolar_id' => $regular->id,
            'nombre' => 'Pedro',
            'apellido_paterno' => 'Gómez',
            'sexo' => Alumno::SEXO_NINO,
        ]);

        $response->assertSessionHasErrors('grado_escolar_id');
        $this->assertDatabaseCount('alumnos', 0);
    }

    public function test_el_modulo_alumnos_rechaza_el_grado_de_estimulacion_temprana(): void
    {
        $user = User::factory()->admin()->create();
        $et = $this->gradoEt();

        $response = $this->actingAs($user)->post(route('alumnos.store'), [
            'grado_escolar_id' => $et->id,
            'nombre' => 'Ana',
            'apellido_paterno' => 'García',
            'sexo' => Alumno::SEXO_NINA,
        ]);

        $response->assertSessionHasErrors('grado_escolar_id');
        $this->assertDatabaseCount('alumnos', 0);
    }

    public function test_profesor_puede_leer_estimulacion_temprana_pero_no_escribir(): void
    {
        $user = User::factory()->profesor()->create();
        $this->gradoEt();

        $this->actingAs($user)->get(route('estimulacion-temprana.index'))->assertOk();
        $this->actingAs($user)->get(route('estimulacion-temprana.create'))->assertForbidden();
    }
}
