<?php

namespace Tests\Feature;

use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmpleadoCrudTest extends TestCase
{
    use RefreshDatabase;

    private function sucursalCentro(): Sucursal
    {
        return Sucursal::where('nombre', 'Sucursal Centro')->firstOrFail();
    }

    public function test_puede_ver_listado_de_empleados_con_todas_las_columnas(): void
    {
        $user = User::factory()->create();
        $empleado = Empleado::factory()->create([
            'nombre' => 'Zafiro',
            'apellido_paterno' => 'Zúñiga',
            'puesto' => 'Dirección General',
            'horario' => 'Lunes a Viernes 8:00 - 14:30',
            'numeros_emergencia' => '55-1234-5678',
            'tipo_sangre' => 'O+',
            'enfermedad' => 'Ninguna',
            'alergias' => 'Ninguna',
            'medicamento' => 'Ninguno',
            'direccion' => 'Calle Falsa 123',
            'telefono_personal' => '55-8765-4321',
            'curp' => 'ZAFI841016MDFRLR00',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('empleados.index', ['q' => 'Zafiro']));

        $response->assertOk();
        $response->assertSee($empleado->nombre_completo);
        $response->assertSee('Área / Puesto');
        $response->assertSee('Horario');
        $response->assertSee('Fecha de nac.');
        $response->assertSee('Números de emergencias');
        $response->assertSee('Tipo sangre');
        $response->assertSee('Enfermedad');
        $response->assertSee('Alergias');
        $response->assertSee('Medicamento');
        $response->assertSee('Dirección');
        $response->assertSee('Teléfono personal');
        $response->assertSee('CURP');
        $response->assertSee($empleado->tipo_sangre);
        $response->assertSee($empleado->curp);
    }

    public function test_puede_crear_un_empleado_con_todos_los_campos_nuevos(): void
    {
        $user = User::factory()->create();
        $sucursal = $this->sucursalCentro();

        $response = $this
            ->actingAs($user)
            ->post(route('empleados.store'), [
                'sucursal_id' => $sucursal->id,
                'nombre' => 'Paulina',
                'apellido_paterno' => 'Pulido',
                'apellido_materno' => 'Cecaldi',
                'puesto' => 'Dirección General',
                'horario' => 'NA',
                'fecha_nacimiento' => '1979-10-08',
                'numeros_emergencia' => 'Manuel 55-5403-6186',
                'tipo_sangre' => 'OH+',
                'enfermedad' => 'Ninguna',
                'alergias' => 'Acido Acetil',
                'medicamento' => 'Ninguno',
                'direccion' => 'Privada Acueducto 22',
                'telefono_personal' => '55-5403-6188',
                'curp' => null,
            ]);

        $response->assertRedirect(route('empleados.index'));
        $response->assertSessionHas('success', 'Empleado creado correctamente.');

        $this->assertDatabaseHas('empleados', [
            'sucursal_id' => $sucursal->id,
            'nombre' => 'Paulina',
            'apellido_paterno' => 'Pulido',
            'apellido_materno' => 'Cecaldi',
            'puesto' => 'Dirección General',
            'horario' => 'NA',
            'numeros_emergencia' => 'Manuel 55-5403-6186',
            'tipo_sangre' => 'OH+',
            'enfermedad' => 'Ninguna',
            'alergias' => 'Acido Acetil',
            'medicamento' => 'Ninguno',
            'direccion' => 'Privada Acueducto 22',
            'telefono_personal' => '55-5403-6188',
            'curp' => null,
            'estatus_id' => Estatus::ACTIVO,
        ]);

        $empleado = Empleado::latest('id')->first();

        $this->assertNotNull($empleado);
        $this->assertSame('Paulina', $empleado->nombre);
        $this->assertSame('1979-10-08', $empleado->fecha_nacimiento->format('Y-m-d'));
    }

    public function test_puede_actualizar_un_empleado_con_los_nuevos_campos(): void
    {
        $user = User::factory()->create();
        $empleado = Empleado::factory()->create();
        $sucursal = $this->sucursalCentro();

        $response = $this
            ->actingAs($user)
            ->put(route('empleados.update', $empleado), [
                'sucursal_id' => $sucursal->id,
                'nombre' => 'Lorena',
                'apellido_paterno' => 'Perez',
                'apellido_materno' => 'Lemmen Meyer',
                'puesto' => 'Dirección General',
                'horario' => 'NA',
                'fecha_nacimiento' => '1982-10-08',
                'numeros_emergencia' => '553-988-3045',
                'tipo_sangre' => 'A-',
                'enfermedad' => 'Ninguna',
                'alergias' => 'Sulfa',
                'medicamento' => 'Ninguno',
                'direccion' => 'Real de acueducto 4',
                'telefono_personal' => '55-5419-7297',
                'curp' => null,
                'estatus_id' => Estatus::INACTIVO,
            ]);

        $response->assertRedirect(route('empleados.index'));
        $response->assertSessionHas('success', 'Empleado actualizado correctamente.');

        $this->assertDatabaseHas('empleados', [
            'id' => $empleado->id,
            'sucursal_id' => $sucursal->id,
            'nombre' => 'Lorena',
            'puesto' => 'Dirección General',
            'horario' => 'NA',
            'tipo_sangre' => 'A-',
            'estatus_id' => Estatus::INACTIVO,
        ]);

        $empleado->refresh();
        $this->assertSame('1982-10-08', $empleado->fecha_nacimiento->format('Y-m-d'));
    }

    public function test_la_vista_show_muestra_los_nuevos_campos(): void
    {
        $user = User::factory()->create();
        $empleado = Empleado::factory()->create([
            'puesto' => 'Maestras',
            'horario' => 'Lunes a Viernes 8:00 - 14:30',
            'tipo_sangre' => 'O+',
            'medicamento' => 'Ninguno',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('empleados.show', $empleado));

        $response->assertOk();
        $response->assertSee('Maestras');
        $response->assertSee('Lunes a Viernes 8:00 - 14:30');
        $response->assertSee('O+');
        $response->assertSee('Ninguno');
    }

    public function test_puede_eliminar_un_empleado_marcandolo_como_eliminado(): void
    {
        $user = User::factory()->create();
        $empleado = Empleado::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete(route('empleados.destroy', $empleado));

        $response->assertRedirect(route('empleados.index'));
        $response->assertSessionHas('success', 'Empleado eliminado correctamente.');

        $this->assertDatabaseHas('empleados', [
            'id' => $empleado->id,
            'estatus_id' => Estatus::ELIMINADO,
        ]);
    }

    public function test_valida_campos_requeridos_al_crear(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('empleados.create'))
            ->post(route('empleados.store'), [
                'sucursal_id' => '',
                'nombre' => '',
                'apellido_paterno' => '',
                'fecha_nacimiento' => 'no-es-una-fecha',
                'email' => 'no-es-correo',
            ]);

        $response->assertRedirect(route('empleados.create'));
        $response->assertSessionHasErrors([
            'sucursal_id',
            'nombre',
            'apellido_paterno',
            'fecha_nacimiento',
            'email',
        ]);
    }

    public function test_puede_buscar_por_curp_y_telefono_personal(): void
    {
        $user = User::factory()->create();
        $empleado = Empleado::factory()->create([
            'curp' => 'QUVM841016MDFRLR00',
            'telefono_personal' => '55-3664-8489',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('empleados.index', ['q' => 'QUVM841016MDFRLR00']));

        $response->assertOk();
        $response->assertSee($empleado->nombre_completo);
    }

    public function test_puede_ordenar_por_nuevas_columnas(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('empleados.index', ['sort' => 'fecha_nacimiento', 'direction' => 'asc']));

        $response->assertOk();
    }
}
