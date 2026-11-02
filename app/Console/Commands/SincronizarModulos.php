<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\CatalogoDeModulos;
use Illuminate\Console\Command;

class SincronizarModulos extends Command
{
    protected $signature = 'modulos:sincronizar';

    protected $description = 'Sync the module catalog and fill any missing permission rows';

    public function handle(): int
    {
        $resultado = CatalogoDeModulos::sincronizar();

        $this->info(sprintf(
            'Catalog synced: %d created, %d updated, %d permission rows created.',
            $resultado['modulos_creados'],
            $resultado['modulos_actualizados'],
            $resultado['permisos_creados'],
        ));

        return self::SUCCESS;
    }
}
