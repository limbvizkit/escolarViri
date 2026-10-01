<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlumnoSexoTest extends TestCase
{
    use RefreshDatabase;

    public function test_puede_crear_alumno_guardando_sexo(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('alumnos.store'), [
                'grado_escolar_id' => Alumno::factory()->make()->grado_escolar_id,
                'nombre' => 'Lucía',
                'apellido_paterno' => 'Martínez',
                'sexo' => Alumno::SEXO_NINA,
            ]);

        $response->assertRedirect(route('alumnos.index'));
        $response->assertSessionHasNoErrors();

        $alumno = Alumno::latest('id')->first();
        $this->assertEquals(Alumno::SEXO_NINA, $alumno->sexo);
        $this->assertEquals('Niña', $alumno->sexo_label);
    }

    public function test_validacion_rechaza_sexo_invalido(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('alumnos.store'), [
                'grado_escolar_id' => Alumno::factory()->make()->grado_escolar_id,
                'nombre' => 'Pedro',
                'apellido_paterno' => 'López',
                'sexo' => '',
            ]);

        $response->assertSessionHasErrors('sexo');

        $responseInvalido = $this
            ->actingAs($user)
            ->post(route('alumnos.store'), [
                'grado_escolar_id' => Alumno::factory()->make()->grado_escolar_id,
                'nombre' => 'Pedro',
                'apellido_paterno' => 'López',
                'sexo' => 'otro',
            ]);

        $responseInvalido->assertSessionHasErrors('sexo');
    }

    public function test_index_filtra_por_sexo(): void
    {
        $user = User::factory()->admin()->create();

        Alumno::factory()->count(2)->create(['sexo' => Alumno::SEXO_NINO]);
        Alumno::factory()->count(3)->create(['sexo' => Alumno::SEXO_NINA]);

        $response = $this
            ->actingAs($user)
            ->get(route('alumnos.index', ['sexo' => Alumno::SEXO_NINO]));

        $response->assertOk();
        $this->assertCount(2, $response->viewData('alumnos'));
        $response->assertSee('Sexo');
        $response->assertSee(route('alumnos.export.pdf', ['sexo' => Alumno::SEXO_NINO]), false);
    }

    public function test_inline_update_actualiza_sexo(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create(['sexo' => Alumno::SEXO_NINO]);

        $response = $this
            ->actingAs($user)
            ->put(route('alumnos.inline-update', $alumno), [
                'sexo' => Alumno::SEXO_NINA,
            ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $alumno->refresh();
        $this->assertEquals(Alumno::SEXO_NINA, $alumno->sexo);
    }
}
