<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Support\CatalogoDeModulos;
use Illuminate\Database\Seeder;

class PermisoSeeder extends Seeder
{
    /**
     * Kept for explicit `db:seed --class=PermisoSeeder` runs: it performs the
     * same idempotent sync as `ModuloSeeder`, because the catalog and the
     * initial matrix are filled by one implementation. The super-admin role is
     * skipped there: it bypasses the matrix in code.
     */
    public function run(): void
    {
        CatalogoDeModulos::sincronizar();
    }
}
