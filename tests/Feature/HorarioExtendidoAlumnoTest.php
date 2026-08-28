<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Estatus;
use App\Models\HorarioExtendido;
use App\Models\User;
use Database\Seeders\HorarioExtendidoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HorarioExtendidoAlumnoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(HorarioExtendidoSeeder::class);
    }

    public function test_horarios_extendidos_seeder_crea_opciones_activas_3_4_5_6(): void
    {
        $this->assertCount(4, HorarioExtendido::all());

        foreach (['3', '4', '5', '6'] as $opcion) {
            $this->assertDatabaseHas('horarios_extendidos', [
                'nombre' => $opcion,
                'estatus_id' => Estatus::ACTIVO,
            ]);
        }
    }

    public function test_puede_crear_alumno_con_horario_extendido(): void
    {
        $user = User::factory()->create();
        $horario = HorarioExtendido::where('nombre', '4')->first();

        $response = $this
            ->actingAs($user)
            ->post(route('alumnos.store'), [
                'grado_escolar_id' => Alumno::factory()->make()->grado_escolar_id,
                'nombre' => 'Juan',
                'apellido_paterno' => 'Pérez',
                'horario_extendido_id' => $horario->id,
            ]);

        $response->assertRedirect(route('alumnos.index'));

        $alumno = Alumno::latest('id')->first();
        $this->assertEquals($horario->id, $alumno->horario_extendido_id);
    }

    public function test_puede_actualizar_alumno_con_horario_extendido(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::factory()->create();
        $horario = HorarioExtendido::where('nombre', '5')->first();

        $response = $this
            ->actingAs($user)
            ->put(route('alumnos.update', $alumno), [
                'grado_escolar_id' => $alumno->grado_escolar_id,
                'nombre' => $alumno->nombre,
                'apellido_paterno' => $alumno->apellido_paterno,
                'horario_extendido_id' => $horario->id,
            ]);

        $response->assertRedirect(route('alumnos.index'));

        $alumno->refresh();
        $this->assertEquals($horario->id, $alumno->horario_extendido_id);
    }

    public function test_index_filtra_por_horario_extendido(): void
    {
        $user = User::factory()->create();
        $horarioTres = HorarioExtendido::where('nombre', '3')->first();
        $horarioCuatro = HorarioExtendido::where('nombre', '4')->first();

        Alumno::factory()->count(2)->create(['horario_extendido_id' => $horarioTres->id]);
        Alumno::factory()->count(3)->create(['horario_extendido_id' => $horarioCuatro->id]);

        $response = $this
            ->actingAs($user)
            ->get(route('alumnos.index', ['horario_extendido_id' => $horarioTres->id]));

        $response->assertOk();
        $this->assertCount(2, $response->viewData('alumnos'));
    }

    public function test_index_muestra_columna_horario_extendido(): void
    {
        $user = User::factory()->create();
        $horario = HorarioExtendido::where('nombre', '6')->first();
        Alumno::factory()->create(['horario_extendido_id' => $horario->id]);

        $response = $this
            ->actingAs($user)
            ->get(route('alumnos.index', ['horario_extendido_id' => $horario->id]));

        $response->assertOk();
        $response->assertSee('Horario extendido');
        $response->assertSee($horario->nombre);
        $response->assertSee(route('alumnos.export.pdf', ['horario_extendido_id' => $horario->id]), false);
    }

    public function test_horario_extendido_id_invalido_falla_validacion(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('alumnos.store'), [
                'grado_escolar_id' => Alumno::factory()->make()->grado_escolar_id,
                'nombre' => 'Juan',
                'apellido_paterno' => 'Pérez',
                'horario_extendido_id' => 9999,
            ]);

        $response->assertSessionHasErrors('horario_extendido_id');
    }
}
