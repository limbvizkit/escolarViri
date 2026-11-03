<?php

use App\Support\CatalogoDeModulos;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Repairs installations where the original catalog migration seeded nothing
 * because `config('modulos.modulos')` was read through a stale config cache
 * that predated the catalog file.
 *
 * Migrations run once, so this repairs existing databases only. Deployments
 * that add or change a module later must run `php artisan modulos:sincronizar`
 * (the same idempotent implementation) as part of the deploy.
 *
 * The catalog file is read directly, so a stale config cache can never leave
 * the tables empty again.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('modulos') || ! Schema::hasTable('permisos')) {
            return;
        }

        CatalogoDeModulos::sincronizar();
    }

    public function down(): void
    {
        // Data repair only: rolling back must not destroy a matrix that a
        // super administrator may have customized.
    }
};
