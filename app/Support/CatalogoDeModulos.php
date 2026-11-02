<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Modulo;
use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

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
     * Parsed definition, memoized per process. The file is part of the code
     * deployment, so it cannot change while a worker is serving requests.
     *
     * @var array{modulos: array<int, array<string, mixed>>, inicial: array<string, array<string, mixed>>, exportaciones?: array<string, array<int, string>>, exporta_default?: array<int, string>}|null
     */
    private static ?array $definicion = null;

    /**
     * @return array{modulos: array<int, array<string, mixed>>, inicial: array<string, array<string, mixed>>, exportaciones?: array<string, array<int, string>>, exporta_default?: array<int, string>}
     *
     * @throws RuntimeException when the catalog file is missing or empty.
     */
    public static function definicion(): array
    {
        if (self::$definicion !== null) {
            return self::$definicion;
        }

        $ruta = config_path('modulos.php');

        if (! is_file($ruta)) {
            throw new RuntimeException("Module catalog file not found at {$ruta}.");
        }

        $definicion = require $ruta;

        if (! is_array($definicion) || ($definicion['modulos'] ?? []) === []) {
            throw new RuntimeException("Module catalog at {$ruta} is empty.");
        }

        return self::$definicion = $definicion;
    }

    /**
     * Roles allowed to export a module's data. Bulk exports are a capability
     * outside the three matrix actions, declared per module in the definition.
     *
     * Fails closed: an unavailable definition means nobody exports.
     *
     * @return array<int, string>
     */
    public static function rolesExportacion(string $clave): array
    {
        try {
            $definicion = self::definicion();
        } catch (Throwable $e) {
            Log::error('Could not read the module definition to resolve export roles.', [
                'modulo' => $clave,
                'error' => $e->getMessage(),
            ]);

            return [];
        }

        return $definicion['exportaciones'][$clave]
            ?? $definicion['exporta_default']
            ?? [];
    }

    /**
     * Sidebar icons, read from the definition so a stale config cache cannot
     * blank them out. Never throws: a broken definition only loses the icons.
     *
     * @return array<string, string>
     */
    public static function iconos(): array
    {
        try {
            $modulos = self::definicion()['modulos'];

            $iconos = [];

            foreach ($modulos as $modulo) {
                if (! is_array($modulo) || ! isset($modulo['clave'], $modulo['icono'])) {
                    continue;
                }

                $iconos[$modulo['clave']] = (string) $modulo['icono'];
            }

            return $iconos;
        } catch (Throwable $e) {
            Log::error('Could not read the module definition to resolve sidebar icons.', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Forget the memoized definition. Used by tests that must prove the file
     * is read rather than a previous parse being reused.
     */
    public static function olvidarDefinicion(): void
    {
        self::$definicion = null;
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

        return DB::transaction(function () use ($definicion): array {
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
        });
    }
}
