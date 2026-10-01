<?php

namespace Database\Factories;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withRole(string $slug): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => Rol::factory()->state(['slug' => $slug]),
        ]);
    }

    public function superAdmin(): static
    {
        return $this->withRole('super-admin');
    }

    public function admin(): static
    {
        return $this->withRole('admin');
    }

    public function director(): static
    {
        return $this->withRole('director');
    }

    public function profesor(): static
    {
        return $this->withRole('profesor');
    }

    public function recepcion(): static
    {
        return $this->withRole('recepcion');
    }
}
