<?php

use App\Support\CatalogoDeModulos;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Repairs installations where the original catalog migration seeded nothing
 * because `config('modulos.modulos')` was read through a stale config cache
 * that predated the catalog file.
 *
 * It runs on every `migrate`, is idempotent, and reads the catalog file
 * directly, so a stale config cache can no longer leave the tables empty.
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
