<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Modulo;
use Illuminate\Database\Seeder;

class ModuloSeeder extends Seeder
{
    /**
     * Sync the catalog table with `config('modulos.modulos')`.
     */
    public function run(): void
    {
        foreach (config('modulos.modulos', []) as $modulo) {
            Modulo::query()->updateOrCreate(
                ['clave' => $modulo['clave']],
                [
                    'nombre' => $modulo['nombre'],
                    'grupo' => $modulo['grupo'],
                    'orden' => $modulo['orden'],
                    'prefijo' => $modulo['prefijo'],
                    'ruta' => $modulo['ruta'],
                    'url' => $modulo['url'],
                ],
            );
        }
    }
}
