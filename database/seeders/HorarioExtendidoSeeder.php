<?php

namespace Database\Seeders;

use App\Models\Estatus;
use App\Models\HorarioExtendido;
use Illuminate\Database\Seeder;

class HorarioExtendidoSeeder extends Seeder
{
    public function run(): void
    {
        $opciones = ['3', '4', '5', '6'];

        foreach ($opciones as $opcion) {
            HorarioExtendido::firstOrCreate(
                ['nombre' => $opcion],
                ['estatus_id' => Estatus::ACTIVO],
            );
        }
    }
}
