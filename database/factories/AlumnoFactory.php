<?php

namespace Database\Factories;

use App\Models\Alumno;
use App\Models\Estatus;
use App\Models\GradoEscolar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alumno>
 */
class AlumnoFactory extends Factory
{
    protected $model = Alumno::class;

    public function definition(): array
    {
        return [
            'grado_escolar_id' => GradoEscolar::factory(),
            'sucursal_id' => null,
            'nombre' => fake()->firstName(),
            'apellido_paterno' => fake()->lastName(),
            'apellido_materno' => fake()->lastName(),
            'fecha_nacimiento' => fake()->date(),
            'horario' => fake()->optional()->word(),
            'inscripcion' => fake()->optional()->randomFloat(2, 100, 5000),
            'reinscripcion' => fake()->optional()->randomFloat(2, 100, 5000),
            'entrevista_inicial' => fake()->optional()->randomFloat(2, 100, 5000),
            'nat_geo' => fake()->optional()->randomFloat(2, 100, 5000),
            'cuota_materiales' => fake()->optional()->randomFloat(2, 100, 5000),
            'fecha_ingreso' => fake()->date(),
            'cuota_mensual' => fake()->optional()->randomFloat(2, 100, 5000),
            'estatus_id' => Estatus::ACTIVO,
            'archivo' => null,
        ];
    }
}
