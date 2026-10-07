<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlumnoIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_nombre_del_alumno_enlaza_al_detalle(): void
    {
        $alumno = Alumno::factory()->create([
            'nombre' => 'Ana',
            'apellido_paterno' => 'Garcia',
        ]);

        $response = $this
            ->actingAs(User::factory()->admin()->create())
            ->get(route('alumnos.index'));

        $response->assertOk();

        $html = $response->getContent();
        $urlDetalle = preg_quote(route('alumnos.show', $alumno), '/');
        $nombre = preg_quote($alumno->nombre, '/');

        $this->assertMatchesRegularExpression(
            '/<a [^>]*href="'.$urlDetalle.'"[^>]*>'.$nombre.'<\/a>/',
            $html
        );
    }
}
