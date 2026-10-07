<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Curso;
use App\Models\CursoAlumno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CursoTest extends TestCase
{
    use RefreshDatabase;

    private function curso(array $atributos = []): Curso
    {
        return Curso::create(array_merge([
            'nombre' => 'Inglés',
            'costo' => 800,
        ], $atributos));
    }

    public function test_lista_los_cursos_y_sus_inscripciones(): void
    {
        $user = User::factory()->admin()->create();
        $curso = $this->curso([
            'nombre' => 'Robótica',
            'costo' => 900,
            'fecha_inicio' => '2026-03-01',
            'fecha_fin' => '2026-06-30',
            'hora_inicio' => '09:00',
            'hora_fin' => '11:00',
        ]);
        $alumno = Alumno::factory()->create(['nombre' => 'Ana', 'apellido_paterno' => 'Garcia']);
        CursoAlumno::create(['curso_id' => $curso->id, 'alumno_id' => $alumno->id]);

        $response = $this->actingAs($user)->get(route('cursos.index'));

        $response->assertOk();
        $response->assertSee('Robótica');
        $response->assertSee('$900.00');
        $response->assertSee('01/03/2026 - 30/06/2026');
        $response->assertSee('09:00 - 11:00');
        $response->assertSee($alumno->nombre_completo);
    }

    public function test_crea_un_curso_con_todos_los_campos(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->post(route('cursos.store'), [
            'nombre' => 'Pintura',
            'costo' => '750',
            'fecha_inicio' => '2026-04-01',
            'fecha_fin' => '2026-07-01',
            'hora_inicio' => '10:00',
            'hora_fin' => '12:00',
        ]);

        $response->assertRedirect(route('cursos.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('cursos', [
            'nombre' => 'Pintura',
            'costo' => '750.00',
            'hora_inicio' => '10:00',
            'hora_fin' => '12:00',
        ]);

        $curso = Curso::firstOrFail();
        $this->assertSame('2026-04-01', $curso->fecha_inicio->format('Y-m-d'));
        $this->assertSame('2026-07-01', $curso->fecha_fin->format('Y-m-d'));
    }

    public function test_rechaza_hora_fin_anterior_a_la_inicio(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->post(route('cursos.store'), [
            'nombre' => 'Música',
            'costo' => '500',
            'hora_inicio' => '11:00',
            'hora_fin' => '09:00',
        ]);

        $response->assertSessionHasErrors('hora_fin');
        $this->assertDatabaseCount('cursos', 0);
    }

    public function test_rechaza_fecha_fin_anterior_a_la_inicio(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->post(route('cursos.store'), [
            'nombre' => 'Danza',
            'costo' => '500',
            'fecha_inicio' => '2026-06-10',
            'fecha_fin' => '2026-06-01',
        ]);

        $response->assertSessionHasErrors('fecha_fin');
        $this->assertDatabaseCount('cursos', 0);
    }

    public function test_agrega_un_alumno_al_curso(): void
    {
        $user = User::factory()->admin()->create();
        $curso = $this->curso();
        $alumno = Alumno::factory()->create();

        $response = $this->actingAs($user)->post(route('cursos.alumnos.store', $curso), [
            'alumno_id' => $alumno->id,
        ]);

        $response->assertRedirect(route('cursos.index'));
        $this->assertDatabaseHas('curso_alumno', [
            'curso_id' => $curso->id,
            'alumno_id' => $alumno->id,
        ]);
    }

    public function test_rechaza_alumno_ya_inscrito(): void
    {
        $user = User::factory()->admin()->create();
        $curso = $this->curso();
        $alumno = Alumno::factory()->create();
        CursoAlumno::create(['curso_id' => $curso->id, 'alumno_id' => $alumno->id]);

        $response = $this->actingAs($user)->post(route('cursos.alumnos.store', $curso), [
            'alumno_id' => $alumno->id,
        ]);

        $response->assertRedirect(route('cursos.alumnos.create', $curso));
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('curso_alumno', 1);
    }

    public function test_inscripcion_multiple_de_alumnos(): void
    {
        $user = User::factory()->admin()->create();
        $curso = $this->curso();
        $alumnos = Alumno::factory()->count(3)->create();

        $response = $this->actingAs($user)->post(route('cursos.alumnos.bulk.store', $curso), [
            'seleccionados' => $alumnos->pluck('id')->all(),
        ]);

        $response->assertRedirect(route('cursos.index'));
        $this->assertDatabaseCount('curso_alumno', 3);
    }

    public function test_quita_un_alumno_del_curso(): void
    {
        $user = User::factory()->admin()->create();
        $curso = $this->curso();
        $alumno = Alumno::factory()->create();
        CursoAlumno::create(['curso_id' => $curso->id, 'alumno_id' => $alumno->id]);

        $response = $this->actingAs($user)->delete(route('cursos.alumnos.destroy', [$curso, $alumno]));

        $response->assertRedirect(route('cursos.index'));
        $this->assertDatabaseCount('curso_alumno', 0);
    }

    public function test_profesor_puede_leer_cursos_pero_no_escribir(): void
    {
        $user = User::factory()->profesor()->create();
        $curso = $this->curso();

        $this->actingAs($user)->get(route('cursos.index'))->assertOk();
        $this->actingAs($user)->get(route('cursos.create'))->assertForbidden();
        $this->actingAs($user)->post(route('cursos.store'))->assertForbidden();
        $this->actingAs($user)->get(route('cursos.edit', $curso))->assertForbidden();
        $this->actingAs($user)->delete(route('cursos.destroy', $curso))->assertForbidden();
    }
}
