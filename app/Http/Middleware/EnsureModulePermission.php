<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Modulo;
use App\Support\CatalogoDeModulos;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureModulePermission
{
    private const VER = 'ver';

    private const ESCRIBIR = 'escribir';

    private const ELIMINAR = 'eliminar';

    /**
     * Last segment of the route name => required action.
     *
     * `download` maps to `escribir` on purpose: alumno/estimulación file
     * downloads were historically restricted to write-capable roles, so a
     * read-only role must keep getting a 403 there.
     */
    private const ACCIONES = [
        'index' => self::VER,
        'show' => self::VER,
        'pdf' => self::VER,
        'export' => self::VER,
        'descargar' => self::VER,
        'download' => self::ESCRIBIR,
        'create' => self::ESCRIBIR,
        'store' => self::ESCRIBIR,
        'edit' => self::ESCRIBIR,
        'update' => self::ESCRIBIR,
        'inline-update' => self::ESCRIBIR,
        'precargar' => self::ESCRIBIR,
        'abonar' => self::ESCRIBIR,
        'updateAbono' => self::ESCRIBIR,
        'destroy' => self::ELIMINAR,
        'delete' => self::ELIMINAR,
    ];

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(403, 'Forbidden');
        }

        if ($user->esSuperAdmin()) {
            return $next($request);
        }

        // The catalog lives in the database, not in config: a stale config
        // cache must never be able to disable authorization. An empty catalog
        // fails closed instead of letting every route through.
        $catalogo = Modulo::query()->pluck('clave', 'prefijo')->all();

        if ($catalogo === []) {
            Log::error('Module catalog is empty; authorization cannot be evaluated.', [
                'user_id' => $user->getKey(),
            ]);

            abort(503, 'Authorization is not configured.');
        }

        $nombre = $request->route()?->getName();

        if ($nombre === null) {
            Log::warning('Unnamed route reached the module permission middleware.', [
                'user_id' => $user->getKey(),
            ]);

            abort(403, 'Forbidden');
        }

        // The dashboard is the only authenticated route whose name has no
        // dot-separated prefix.
        if ($nombre === 'dashboard') {
            return $user->puedeVer('dashboard')
                ? $next($request)
                : abort(403, 'Forbidden');
        }

        $segmentos = explode('.', $nombre);
        $clave = $catalogo[$segmentos[0]] ?? null;

        // A route inside a permission group that belongs to no catalogued
        // module is a configuration error: deny instead of opening it.
        if ($clave === null) {
            Log::warning('Route prefix is not part of the module catalog. Run modulos:sincronizar.', [
                'route' => $nombre,
                'user_id' => $user->getKey(),
            ]);

            abort(403, 'Forbidden');
        }

        // Bulk exports are an administrative capability, not a module action:
        // each module declares which roles may export, defaulting to admin-only.
        // Read from the definition file, never from the compiled config cache.
        if ($this->esExportacion($segmentos)) {
            $rolesExportacion = CatalogoDeModulos::rolesExportacion($clave);

            if (! in_array($user->rol?->slug, $rolesExportacion, true)
                || ! $user->puedeVer($clave)) {
                abort(403, 'Forbidden');
            }

            return $next($request);
        }

        $accion = $this->resolverAccion($segmentos);

        if ($accion === null) {
            // Fail loud instead of silently opening a new route.
            Log::warning('Unmapped module action in EnsureModulePermission.', [
                'route' => $nombre,
                'user_id' => $user->getKey(),
            ]);

            abort(403, 'Forbidden');
        }

        $permitido = match ($accion) {
            self::VER => $user->puedeVer($clave),
            self::ESCRIBIR => $user->puedeEscribir($clave),
            self::ELIMINAR => $user->puedeEliminar($clave),
        };

        if (! $permitido) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }

    /**
     * @param  array<int, string>  $segmentos
     */
    private function esExportacion(array $segmentos): bool
    {
        foreach ($segmentos as $segmento) {
            if (str_starts_with($segmento, 'export')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, string>  $segmentos
     */
    private function resolverAccion(array $segmentos): ?string
    {
        $ultimo = $segmentos[count($segmentos) - 1] ?? null;

        if ($ultimo === null) {
            return null;
        }

        return self::ACCIONES[$ultimo] ?? null;
    }
}
