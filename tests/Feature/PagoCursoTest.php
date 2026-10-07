<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Curso;
use App\Models\CursoAlumno;
use App\Models\Pago;
use App\Models\PagoCurso;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagoCursoTest extends TestCase
{
    use RefreshDatabase;

    private function inscribir(Alumno $alumno, Curso $curso): void
    {
        CursoAlumno::create([
            'alumno_id' => $alumno->id,
            'curso_id' => $curso->id,
        ]);
    }

    private function registrarPagoCurso(User $user, Alumno $alumno, Curso $curso, string $mes, string $monto): void
    {
        $this->actingAs($user)->post(route('pagos-cursos.store'), [
            'alumno_id' => $alumno->id,
            'curso_id' => $curso->id,
            'mes' => $mes,
            'monto' => $monto,
        ])->assertSessionHasNoErrors();
    }

    public function test_lista_los_pagos_de_cursos_con_fecha_de_registro(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create(['nombre' => 'Ana', 'apellido_paterno' => 'Garcia']);
        $curso = Curso::create(['nombre' => 'Robótica', 'costo' => 500]);
        $this->inscribir($alumno, $curso);

        $pago = PagoCurso::create([
            'alumno_id' => $alumno->id,
            'curso_id' => $curso->id,
            'mes' => '2026-03',
            'monto' => 500,
            'observaciones' => 'Pago de marzo',
        ]);

        $response = $this->actingAs($user)->get(route('pagos-cursos.index'));

        $response->assertOk();
        $response->assertSee($alumno->nombre_completo);
        $response->assertSee('Robótica');
        $response->assertSee(Pago::mesLabel('2026-03'));
        $response->assertSee('$500.00');
        $response->assertSee('Pago de marzo');
        $response->assertSee($pago->created_at->format('d/m/Y'));
    }

    public function test_crea_un_pago_para_un_curso_inscrito(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $curso = Curso::create(['nombre' => 'Música', 'costo' => 300]);
        $this->inscribir($alumno, $curso);

        $response = $this->actingAs($user)->post(route('pagos-cursos.store'), [
            'alumno_id' => $alumno->id,
            'curso_id' => $curso->id,
            'mes' => '2026-04',
            'monto' => '300',
            'observaciones' => 'Abril',
        ]);

        $response->assertRedirect(route('pagos-cursos.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('pagos_cursos', [
            'alumno_id' => $alumno->id,
            'curso_id' => $curso->id,
            'mes' => '2026-04',
            'monto' => '300.00',
        ]);

        $this->assertNotNull(PagoCurso::first()->created_at);
    }

    public function test_rechaza_un_curso_no_inscrito_para_el_alumno(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $inscrito = Curso::create(['nombre' => 'Danza', 'costo' => 200]);
        $noInscrito = Curso::create(['nombre' => 'Ajedrez', 'costo' => 150]);
        $this->inscribir($alumno, $inscrito);

        $response = $this->actingAs($user)->post(route('pagos-cursos.store'), [
            'alumno_id' => $alumno->id,
            'curso_id' => $noInscrito->id,
            'mes' => '2026-04',
            'monto' => '150',
        ]);

        $response->assertSessionHasErrors('curso_id');
        $this->assertDatabaseCount('pagos_cursos', 0);
    }

    public function test_la_pagina_de_alta_solo_muestra_los_cursos_inscritos(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $inscrito = Curso::create(['nombre' => 'Pintura', 'costo' => 100]);
        $otro = Curso::create(['nombre' => 'Natación', 'costo' => 100]);
        $this->inscribir($alumno, $inscrito);

        $response = $this->actingAs($user)->get(route('pagos-cursos.create'));

        $response->assertOk();
        $response->assertSee('Pintura');
        $response->assertDontSee('Natación');
    }

    public function test_actualiza_un_pago_de_curso(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $curso = Curso::create(['nombre' => 'Teatro', 'costo' => 100]);
        $this->inscribir($alumno, $curso);

        $pago = PagoCurso::create([
            'alumno_id' => $alumno->id,
            'curso_id' => $curso->id,
            'mes' => '2026-05',
            'monto' => 100,
        ]);

        $response = $this->actingAs($user)->put(route('pagos-cursos.update', $pago), [
            'alumno_id' => $alumno->id,
            'curso_id' => $curso->id,
            'mes' => '2026-05',
            'monto' => '250',
            'observaciones' => 'Ajuste',
        ]);

        $response->assertRedirect(route('pagos-cursos.index'));

        $this->assertDatabaseHas('pagos_cursos', [
            'id' => $pago->id,
            'monto' => '250.00',
            'observaciones' => 'Ajuste',
        ]);
    }

    public function test_elimina_un_pago_de_curso(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $curso = Curso::create(['nombre' => 'Cocina', 'costo' => 100]);
        $this->inscribir($alumno, $curso);

        $pago = PagoCurso::create([
            'alumno_id' => $alumno->id,
            'curso_id' => $curso->id,
            'mes' => '2026-05',
            'monto' => 100,
        ]);

        $response = $this->actingAs($user)->delete(route('pagos-cursos.destroy', $pago));

        $response->assertRedirect(route('pagos-cursos.index'));
        $this->assertDatabaseCount('pagos_cursos', 0);
    }

    public function test_suma_los_pagos_de_cursos_en_el_campo_cursos_del_pago_mensual(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $cursoA = Curso::create(['nombre' => 'Fútbol', 'costo' => 100]);
        $cursoB = Curso::create(['nombre' => 'Música', 'costo' => 100]);
        $this->inscribir($alumno, $cursoA);
        $this->inscribir($alumno, $cursoB);

        $mes = '2026-06';

        $this->registrarPagoCurso($user, $alumno, $cursoA, $mes, '300');

        $this->assertDatabaseHas('pagos', [
            'alumno_id' => $alumno->id,
            'mes' => $mes,
            'cursos' => '300.00',
        ]);

        $this->registrarPagoCurso($user, $alumno, $cursoB, $mes, '200');

        // Un solo pago mensual con la sumatoria de ambos cursos.
        $this->assertDatabaseCount('pagos', 1);
        $this->assertDatabaseHas('pagos', [
            'alumno_id' => $alumno->id,
            'mes' => $mes,
            'cursos' => '500.00',
        ]);
    }

    public function test_al_eliminar_un_pago_de_curso_recalcula_el_campo_cursos(): void
    {
        $user = User::factory()->admin()->create();
        $alumno = Alumno::factory()->create();
        $cursoA = Curso::create(['nombre' => 'Fútbol', 'costo' => 100]);
        $cursoB = Curso::create(['nombre' => 'Música', 'costo' => 100]);
        $this->inscribir($alumno, $cursoA);
        $this->inscribir($alumno, $cursoB);

        $mes = '2026-06';
        $this->registrarPagoCurso($user, $alumno, $cursoA, $mes, '300');
        $this->registrarPagoCurso($user, $alumno, $cursoB, $mes, '200');

        $pagoA = PagoCurso::where('alumno_id', $alumno->id)->where('curso_id', $cursoA->id)->firstOrFail();
        $pagoB = PagoCurso::where('alumno_id', $alumno->id)->where('curso_id', $cursoB->id)->firstOrFail();

        $this->actingAs($user)->delete(route('pagos-cursos.destroy', $pagoA))->assertRedirect(route('pagos-cursos.index'));

        $this->assertDatabaseHas('pagos', [
            'alumno_id' => $alumno->id,
            'mes' => $mes,
            'cursos' => '200.00',
        ]);

        $this->actingAs($user)->delete(route('pagos-cursos.destroy', $pagoB))->assertRedirect(route('pagos-cursos.index'));

        $this->assertDatabaseHas('pagos', [
            'alumno_id' => $alumno->id,
            'mes' => $mes,
            'cursos' => null,
        ]);
    }

    public function test_profesor_no_puede_acceder_a_pagos_de_cursos(): void
    {
        $user = User::factory()->profesor()->create();

        $this->actingAs($user)->get(route('pagos-cursos.index'))->assertForbidden();
        $this->actingAs($user)->get(route('pagos-cursos.create'))->assertForbidden();
    }
}
