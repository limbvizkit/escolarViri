<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Support\CatalogoDeModulos;
use Illuminate\Database\Seeder;

class PermisoSeeder extends Seeder
{
    /**
     * Fill the initial matrix from the module definition.
     *
     * Only missing rows are created, so re-seeding never overwrites a matrix a
     * super administrator customized. The super-admin role is skipped: it
     * bypasses the matrix in code.
     */
    public function run(): void
    {
        CatalogoDeModulos::sincronizar();
    }
}
