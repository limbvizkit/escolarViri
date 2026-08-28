<?php

namespace Database\Factories;

use App\Models\Estatus;
use App\Models\GradoEscolar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradoEscolar>
 */
class GradoEscolarFactory extends Factory
{
    protected $model = GradoEscolar::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->word(),
            'slug' => fake()->unique()->slug(),
            'estatus_id' => Estatus::ACTIVO,
        ];
    }
}
