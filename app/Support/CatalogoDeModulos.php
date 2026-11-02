<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Modulo;
use App\Models\Permiso;
use App\Models\Rol;
use RuntimeException;

/**
 * Single implementation of "make the module catalog and the initial permission
 * matrix match what the code declares".
 *
 * The definition is read straight from the config file instead of through
 * `config()`. A stale `bootstrap/cache/config.php` from a previous deploy does
 * not contain a newly added config file, so `config('modulos.modulos')` can
 * legitimately return null while the file exists on disk. Seeding from that
 * value would silently insert nothing and leave the app without a sidebar and
 * without authorization.
 */
final class CatalogoDeModulos
{
    /**
     * @return array{modulos: array<int, array<string, mixed>>, inicial: array<string, array<string, mixed>>}
     *
     * @throws RuntimeException when the catalog file is missing or empty.
     */
    public static function definicion(): array
    {
        $ruta = config_path('modulos.php');

        if (! is_file($ruta)) {
            throw new RuntimeException("Module catalog file not found at {$ruta}.");
        }

        $definicion = require $ruta;

        if (! is_array($definicion) || ($definicion['modulos'] ?? []) === []) {
            throw new RuntimeException("Module catalog at {$ruta} is empty.");
        }

        return $definicion;
    }

    /**
     * Upsert the catalog rows and fill any missing permission row. Existing
     * permission rows are never overwritten, so a matrix a super administrator
     * customized is preserved.
     *
     * @return array{modulos_creados: int, modulos_actualizados: int, permisos_creados: int}
     */
    public static function sincronizar(): array
    {
        $definicion = self::definicion();

        $creados = 0;
        $actualizados = 0;

        foreach ($definicion['modulos'] as $modulo) {
            $existia = Modulo::query()->where('clave', $modulo['clave'])->exists();

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

            $existia ? $actualizados++ : $creados++;
        }

        $permisos = 0;
        $modulos = Modulo::query()->get();
        $roles = Rol::query()->where('slug', '!=', 'super-admin')->get();

        foreach ($roles as $rol) {
            foreach ($modulos as $modulo) {
                $acciones = $definicion['inicial'][$modulo->clave] ?? [];

                $permiso = Permiso::query()->firstOrCreate(
                    ['rol_id' => $rol->id, 'modulo_id' => $modulo->id],
                    [
                        'puede_ver' => in_array($rol->slug, $acciones['ver'] ?? [], true),
                        'puede_escribir' => in_array($rol->slug, $acciones['escribir'] ?? [], true),
                        'puede_eliminar' => in_array($rol->slug, $acciones['eliminar'] ?? [], true),
                    ],
                );

                if ($permiso->wasRecentlyCreated) {
                    $permisos++;
                }
            }
        }

        return [
            'modulos_creados' => $creados,
            'modulos_actualizados' => $actualizados,
            'permisos_creados' => $permisos,
        ];
    }
}
