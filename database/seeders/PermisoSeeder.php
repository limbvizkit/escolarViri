<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Modulo;
use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Database\Seeder;

class PermisoSeeder extends Seeder
{
    /**
     * Fill the initial matrix from `config('modulos.inicial')`.
     *
     * Only missing rows are created (`firstOrCreate`) so re-seeding never
     * overwrites a matrix a super-admin has customized. The super-admin role is
     * skipped: it bypasses the matrix in code.
     */
    public function run(): void
    {
        $modulos = Modulo::query()->get();

        if ($modulos->isEmpty()) {
            return;
        }

        Rol::query()->where('slug', '!=', 'super-admin')->each(function (Rol $rol) use ($modulos): void {
            foreach ($modulos as $modulo) {
                $acciones = config('modulos.inicial.'.$modulo->clave, []);

                Permiso::query()->firstOrCreate(
                    ['rol_id' => $rol->id, 'modulo_id' => $modulo->id],
                    [
                        'puede_ver' => in_array($rol->slug, $acciones['ver'] ?? [], true),
                        'puede_escribir' => in_array($rol->slug, $acciones['escribir'] ?? [], true),
                        'puede_eliminar' => in_array($rol->slug, $acciones['eliminar'] ?? [], true),
                    ],
                );
            }
        });
    }
}
