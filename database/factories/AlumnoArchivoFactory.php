<?php

namespace Database\Factories;

use App\Models\Alumno;
use App\Models\AlumnoArchivo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlumnoArchivo>
 */
class AlumnoArchivoFactory extends Factory
{
    protected $model = AlumnoArchivo::class;

    public function definition(): array
    {
        return [
            'alumno_id' => Alumno::factory(),
            'archivo' => 'alumnos/'.fake()->uuid.'.pdf',
            'nombre_original' => fake()->word().'.pdf',
        ];
    }
}
