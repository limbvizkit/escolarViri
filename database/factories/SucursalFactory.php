<?php

namespace Database\Factories;

use App\Models\Escuela;
use App\Models\Estatus;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sucursal>
 */
class SucursalFactory extends Factory
{
    protected $model = Sucursal::class;

    public function definition(): array
    {
        return [
            'escuela_id' => Escuela::factory(),
            'nombre' => fake()->company(),
            'direccion' => fake()->address(),
            'telefono' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'estatus_id' => Estatus::ACTIVO,
        ];
    }
}
