<?php

namespace Tests\Feature;

use App\Models\Empleado;
use App\Models\Escuela;
use App\Models\Estatus;
use App\Models\Sucursal;
use App\Models\User;
use Database\Seeders\EmpleadoReplacementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpleadoReplacementSeederTest extends TestCase
{
    use RefreshDatabase;

    private function defaultSucursal(): Sucursal
    {
        $sucursal = Sucursal::where('nombre', 'Sucursal Centro')->first();

        if ($sucursal !== null) {
            return $sucursal;
        }

        $escuela = Escuela::create([
            'nombre' => 'Instituto Horizonte',
            'clave' => 'IH-001',
        ]);

        return Sucursal::create([
            'escuela_id' => $escuela->id,
            'nombre' => 'Sucursal Centro',
        ]);
    }

    public function test_reemplaza_todos_los_empleados_y_vincula_solo_a_viri(): void
    {
        $sucursalCentro = $this->defaultSucursal();
        $otraSucursal = Sucursal::factory()->create();

        $empleadoCentro = Empleado::create([
            'sucursal_id' => $sucursalCentro->id,
            'nombre' => 'Antiguo',
            'apellido_paterno' => 'Centro',
            'puesto' => 'Otro',
            'estatus_id' => 1,
        ]);

        $empleadoOtra = Empleado::create([
            'sucursal_id' => $otraSucursal->id,
            'nombre' => 'Antiguo',
            'apellido_paterno' => 'Otra',
            'puesto' => 'Otro',
            'estatus_id' => 1,
        ]);

        $viri = User::factory()->create([
            'name' => 'viri',
            'email' => 'viri@educacion.edu.mx',
            'empleado_id' => $empleadoCentro->id,
        ]);

        $otroUsuario = User::factory()->create([
            'name' => 'otro',
            'empleado_id' => $empleadoCentro->id,
        ]);

        $this->seed(EmpleadoReplacementSeeder::class);

        $this->assertDatabaseMissing('empleados', [
            'id' => $empleadoCentro->id,
        ]);

        $this->assertDatabaseMissing('empleados', [
            'id' => $empleadoOtra->id,
        ]);

        $this->assertDatabaseCount('empleados', 20);

        $this->assertDatabaseHas('empleados', [
            'sucursal_id' => $sucursalCentro->id,
            'nombre' => 'Ma. Viridiana',
            'apellido_paterno' => 'Gómez',
            'apellido_materno' => 'Fierros',
            'puesto' => 'Administración',
            'horario' => 'Lunes a Viernes 8:00 - 15:00',
            'tipo_sangre' => 'O+',
            'telefono_personal' => '442-569-3051',
            'curp' => 'GOFV840522MJC00',
        ]);

        $empleadoViri = Empleado::where('nombre', 'Ma. Viridiana')
            ->where('apellido_paterno', 'Gómez')
            ->where('apellido_materno', 'Fierros')
            ->first();

        $this->assertNotNull($empleadoViri);
        $this->assertSame('1984-05-22', $empleadoViri->fecha_nacimiento->format('Y-m-d'));

        $viri->refresh();
        $otroUsuario->refresh();

        $this->assertSame($empleadoViri->id, $viri->empleado_id);
        $this->assertNull($otroUsuario->empleado_id);
    }

    public function test_es_idempotente(): void
    {
        $this->defaultSucursal();

        $this->seed(EmpleadoReplacementSeeder::class);
        $this->seed(EmpleadoReplacementSeeder::class);

        $this->assertDatabaseCount('empleados', 20);
    }

    public function test_no_elimina_usuarios_al_desvincular_empleados(): void
    {
        $sucursal = $this->defaultSucursal();
        $empleado = Empleado::create([
            'sucursal_id' => $sucursal->id,
            'nombre' => 'Temporal',
            'apellido_paterno' => 'Usuario',
            'puesto' => 'Temporal',
            'estatus_id' => 1,
        ]);

        User::factory()->create([
            'name' => 'viri',
            'email' => 'viri@educacion.edu.mx',
            'empleado_id' => $empleado->id,
        ]);

        User::factory()->create([
            'name' => 'otro',
            'empleado_id' => $empleado->id,
        ]);

        $this->seed(EmpleadoReplacementSeeder::class);

        $this->assertDatabaseCount('users', 2);
    }

    public function test_lanza_excepcion_si_no_hay_sucursal_activa(): void
    {
        Sucursal::query()->update(['estatus_id' => Estatus::INACTIVO]);

        $this->expectException(\RuntimeException::class);

        $this->seed(EmpleadoReplacementSeeder::class);
    }
}
