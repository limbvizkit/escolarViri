<?php

namespace Database\Factories;

use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Empleado>
 */
class EmpleadoFactory extends Factory
{
    protected $model = Empleado::class;

    public function definition(): array
    {
        return [
            'sucursal_id' => Sucursal::factory(),
            'nombre' => fake()->firstName(),
            'apellido_paterno' => fake()->lastName(),
            'apellido_materno' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'telefono' => fake()->phoneNumber(),
            'puesto' => fake()->jobTitle(),
            'horario' => 'Lunes a Viernes 8:00 - 14:30',
            'fecha_nacimiento' => fake()->date(),
            'numeros_emergencia' => fake()->phoneNumber(),
            'tipo_sangre' => fake()->randomElement(['O+', 'A+', 'B+', 'AB+']),
            'enfermedad' => null,
            'alergias' => null,
            'medicamento' => null,
            'direccion' => fake()->address(),
            'telefono_personal' => fake()->phoneNumber(),
            'curp' => strtoupper(fake()->bothify('????######????###?')),
            'estatus_id' => Estatus::ACTIVO,
        ];
    }
}
