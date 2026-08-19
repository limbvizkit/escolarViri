<?php

namespace Database\Seeders;

use App\Models\Empleado;
use App\Models\Escuela;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            GradoEscolarSeeder::class,
            FormaPagoSeeder::class,
        ]);

        $escuela = Escuela::create([
            'nombre' => 'Instituto Educativo Horizonte',
            'clave' => 'IEH-001',
            'direccion' => 'Av. Principal 123',
            'telefono' => '555-1234',
            'email' => 'contacto@horizonte.edu.mx',
        ]);

        $sucursal = Sucursal::create([
            'escuela_id' => $escuela->id,
            'nombre' => 'Sucursal Centro',
            'direccion' => 'Calle Juárez 45',
            'telefono' => '555-5678',
            'email' => 'centro@horizonte.edu.mx',
        ]);

        $empleado = Empleado::create([
            'sucursal_id' => $sucursal->id,
            'nombre' => 'Juan',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'García',
            'email' => 'direccion@horizonte.edu.mx',
            'telefono' => '555-9999',
            'puesto' => 'Director',
        ]);

        $adminRol = Rol::where('slug', 'super-admin')->first();

        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@horizonte.edu.mx',
            'password' => 'password123',
            'empleado_id' => $empleado->id,
            'role_id' => $adminRol->id,
        ]);
    }
}
