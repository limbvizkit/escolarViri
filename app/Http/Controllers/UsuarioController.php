<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $query = User::with(['empleado', 'rol']);

        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->input('role_id'));
        }

        $usuarios = $this->paginateOrdered(
            $query,
            $request,
            ['id', 'name', 'email'],
            'name',
        );

        $filtros = [
            ['name' => 'role_id', 'label' => 'Rol', 'options' => Rol::orderBy('nombre')->pluck('nombre', 'id')->all()],
        ];

        return view('usuarios.index', compact('usuarios', 'filtros'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        $roles = $this->availableRoles();
        $empleados = Empleado::with('sucursal')->
            whereDoesntHave('usuario')->orderBy('apellido_paterno')->get();

        return view('usuarios.create', compact('roles', 'empleados'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $this->normalizeEmailInput($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'empleado_id' => ['nullable', 'exists:empleados,id'],
        ]);

        $rol = null;

        if (! empty($validated['role_id'])) {
            $rol = Rol::findOrFail($validated['role_id']);
            $this->authorize('assignRole', [User::class, $rol]);
        }

        $usuario = new User;
        $usuario->fill($validated);
        $usuario->role_id = $rol?->id;
        $usuario->save();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function show(User $usuario): View
    {
        $this->authorize('view', $usuario);

        $usuario->load(['empleado', 'rol']);

        return view('usuarios.show', compact('usuario'));
    }

    public function edit(User $usuario): View
    {
        $this->authorize('update', $usuario);

        $roles = $this->availableRoles();
        $empleados = Empleado::with('sucursal')->orderBy('apellido_paterno')->get();

        return view('usuarios.edit', compact('usuario', 'roles', 'empleados'));
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $this->authorize('update', $usuario);

        $this->normalizeEmailInput($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$usuario->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'empleado_id' => ['nullable', 'exists:empleados,id'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $roleChanged = array_key_exists('role_id', $validated)
            && (int) $validated['role_id'] !== (int) $usuario->role_id;

        if ($roleChanged && $validated['role_id'] !== null) {
            $rol = Rol::findOrFail($validated['role_id']);
            $this->authorize('assignRole', [User::class, $rol]);
        }

        $usuario->update($validated);

        if (array_key_exists('role_id', $validated)) {
            $usuario->role_id = $validated['role_id'];
            $usuario->save();
        }

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario): RedirectResponse
    {
        $this->authorize('delete', $usuario);

        $usuario->delete();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }

    /**
     * Lowercase the email before validation so the `unique` rule (and SQLite's
     * case-sensitive comparison) catches accounts that only differ by case.
     */
    private function normalizeEmailInput(Request $request): void
    {
        $email = $request->input('email');

        if (is_string($email)) {
            $request->merge(['email' => strtolower(trim($email))]);
        }
    }

    /**
     * Roles assignable by the current actor. Non super-admins cannot pick the
     * super-admin role in the UI; the backend policy remains the real barrier.
     */
    private function availableRoles(): Collection
    {
        $query = Rol::query();

        if (Auth::user()?->rol?->slug !== 'super-admin') {
            $query->where('slug', '!=', 'super-admin');
        }

        return $query->orderBy('nombre')->get();
    }
}
