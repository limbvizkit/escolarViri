<?php

declare(strict_types=1);

namespace App\Http\Middleware;

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

        $nombre = $request->route()?->getName();

        if ($nombre === null) {
            return $next($request);
        }

        // The dashboard is the only authenticated route whose name has no
        // dot-separated prefix.
        if ($nombre === 'dashboard') {
            return $user->puedeVer('dashboard')
                ? $next($request)
                : abort(403, 'Forbidden');
        }

        $segmentos = explode('.', $nombre);
        $clave = $this->moduloPorPrefijo($segmentos[0]);

        // Route belongs to no module (logout, portal, webhooks...).
        if ($clave === null) {
            return $next($request);
        }

        // Bulk exports are an administrative capability, not a module action:
        // each module declares which roles may export, defaulting to admin-only.
        if ($this->esExportacion($segmentos)) {
            $rolesExportacion = config('modulos.exportaciones.'.$clave, config('modulos.exporta_default', []));

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

    private function moduloPorPrefijo(string $prefijo): ?string
    {
        foreach (config('modulos.modulos', []) as $modulo) {
            if ($modulo['prefijo'] === $prefijo) {
                return $modulo['clave'];
            }
        }

        return null;
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
