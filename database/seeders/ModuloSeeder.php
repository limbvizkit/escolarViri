<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Support\CatalogoDeModulos;
use Illuminate\Database\Seeder;

class ModuloSeeder extends Seeder
{
    /**
     * Sync the catalog table with the module definition.
     */
    public function run(): void
    {
        CatalogoDeModulos::sincronizar();
    }
}
