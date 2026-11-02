<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Modulo;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RolController extends Controller
{
    public function index(Request $request): View
    {
        $this->autorizar('ver');

        $query = Rol::withCount('usuarios');

        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        $roles = $this->paginateOrdered(
            $query,
            $request,
            ['id', 'nombre', 'slug', 'usuarios_count'],
            'nombre',
        );

        $filtros = [];

        return view('roles.index', compact('roles', 'filtros'));
    }

    public function create(): View
    {
        $this->autorizar('escribir');

        return view('roles.create', $this->datosMatriz());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->autorizar('escribir');

        $validated = $this->validar($request);

        DB::transaction(function () use ($validated, $request): void {
            $rol = Rol::create([
                'nombre' => $validated['nombre'],
                'slug' => $validated['slug'] ?? Str::slug($validated['nombre']),
            ]);

            $this->sincronizarPermisos($rol, (array) $request->input('permisos', []));
        });

        return redirect()->route('roles.index')
            ->with('success', 'Rol creado correctamente.');
    }

    public function show(Rol $rol): View
    {
        $this->autorizar('ver');

        $rol->load(['usuarios', 'permisos.modulo']);

        return view('roles.show', ['rol' => $rol] + $this->datosMatriz($rol));
    }

    public function edit(Rol $rol): View
    {
        $this->autorizar('escribir');

        return view('roles.edit', ['rol' => $rol] + $this->datosMatriz($rol));
    }

    public function update(Request $request, Rol $rol): RedirectResponse
    {
        $this->autorizar('escribir');

        $validated = $this->validar($request, $rol);

        DB::transaction(function () use ($rol, $validated, $request): void {
            $rol->update([
                'nombre' => $validated['nombre'],
                'slug' => $validated['slug'] ?? Str::slug($validated['nombre']),
            ]);

            $this->sincronizarPermisos($rol, (array) $request->input('permisos', []));
        });

        return redirect()->route('roles.index')
            ->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Rol $rol): RedirectResponse
    {
        $this->autorizar('eliminar');

        DB::transaction(function () use ($rol): void {
            $rol->permisos()->delete();
            $rol->delete();
        });

        return redirect()->route('roles.index')
            ->with('success', 'Rol eliminado correctamente.');
    }

    /**
     * @return array{modulos: Collection<string, \Illuminate\Database\Eloquent\Collection<int, Modulo>>, permisos: Collection<int, Permiso>}
     */
    private function datosMatriz(?Rol $rol = null): array
    {
        $modulos = Modulo::query()->ordenado()->get()->groupBy('grupo');

        $permisos = $rol !== null
            ? $rol->permisos->keyBy('modulo_id')
            : collect();

        return compact('modulos', 'permisos');
    }

    /**
     * @return array<string, mixed>
     */
    private function validar(Request $request, ?Rol $rol = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:roles,slug'.($rol !== null ? ','.$rol->id : '')],
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['array'],
            'permisos.*.ver' => ['nullable', 'boolean'],
            'permisos.*.escribir' => ['nullable', 'boolean'],
            'permisos.*.eliminar' => ['nullable', 'boolean'],
        ]);
    }

    /**
     * Persist one row per catalog module, normalizing write/delete to imply
     * view. Only super-admin may change the matrix, and the super-admin role is
     * never written because it bypasses the matrix in code.
     *
     * @param  array<int|string, array<string, mixed>>  $payload
     */
    private function sincronizarPermisos(Rol $rol, array $payload): void
    {
        if ($rol->slug === 'super-admin' || ! $this->actor()?->esSuperAdmin()) {
            return;
        }

        Modulo::query()->each(function (Modulo $modulo) use ($rol, $payload): void {
            $entrada = $payload[$modulo->id] ?? [];

            $ver = filter_var($entrada['ver'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $escribir = filter_var($entrada['escribir'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $eliminar = filter_var($entrada['eliminar'] ?? false, FILTER_VALIDATE_BOOLEAN);

            if ($escribir || $eliminar) {
                $ver = true;
            }

            Permiso::query()->updateOrCreate(
                ['rol_id' => $rol->id, 'modulo_id' => $modulo->id],
                [
                    'puede_ver' => $ver,
                    'puede_escribir' => $escribir,
                    'puede_eliminar' => $eliminar,
                ],
            );
        });
    }

    private function actor(): ?User
    {
        return request()->user();
    }

    private function autorizar(string $accion): void
    {
        $user = $this->actor();

        $permitido = match ($accion) {
            'ver' => $user?->puedeVer('roles'),
            'escribir' => $user?->puedeEscribir('roles'),
            'eliminar' => $user?->puedeEliminar('roles'),
            default => false,
        };

        abort_unless((bool) $permitido, 403, 'Forbidden');
    }
}
