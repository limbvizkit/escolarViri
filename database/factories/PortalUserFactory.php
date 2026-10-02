<?php

namespace Database\Factories;

use App\Models\GradoEscolar;
use App\Models\PortalUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PortalUser>
 */
class PortalUserFactory extends Factory
{
    protected $model = PortalUser::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'alumno_nombre' => fake()->name(),
            'grado_escolar_id' => GradoEscolar::factory(),
            'password' => 'password',
            'must_change_password' => false,
        ];
    }
}
