<?php

namespace Database\Factories;

use App\Models\Rol;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rol>
 */
class RolFactory extends Factory
{
    protected $model = Rol::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->jobTitle(),
            'slug' => fake()->unique()->slug(),
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'nombre' => 'Super Administrador',
            'slug' => 'super-admin',
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'nombre' => 'Administrador',
            'slug' => 'admin',
        ]);
    }

    public function director(): static
    {
        return $this->state(fn (array $attributes) => [
            'nombre' => 'Director',
            'slug' => 'director',
        ]);
    }

    public function profesor(): static
    {
        return $this->state(fn (array $attributes) => [
            'nombre' => 'Profesor',
            'slug' => 'profesor',
        ]);
    }

    public function recepcion(): static
    {
        return $this->state(fn (array $attributes) => [
            'nombre' => 'Recepción',
            'slug' => 'recepcion',
        ]);
    }
}
