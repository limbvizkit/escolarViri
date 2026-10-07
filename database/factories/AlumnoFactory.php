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
            'sexo' => fake()->randomElement([Alumno::SEXO_NINO, Alumno::SEXO_NINA]),
            'fecha_nacimiento' => fake()->date(),
            'horario' => fake()->optional()->word(),
            'inscripcion' => fake()->randomElement(Alumno::CONCEPTOS_ESTADO),
            'reinscripcion' => fake()->randomElement(Alumno::CONCEPTOS_ESTADO),
            'entrevista_inicial' => fake()->randomElement(Alumno::CONCEPTOS_ESTADO),
            'nat_geo' => fake()->randomElement(Alumno::CONCEPTOS_ESTADO),
            'cuota_materiales' => fake()->randomElement(Alumno::CONCEPTOS_ESTADO),
            'fecha_ingreso' => fake()->date(),
            'cuota_mensual' => fake()->optional()->randomFloat(2, 100, 5000),
            'estatus_id' => Estatus::ACTIVO,
            'archivo' => null,
        ];
    }
}
