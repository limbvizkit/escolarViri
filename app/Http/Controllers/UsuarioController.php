<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
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
        $roles = Rol::orderBy('nombre')->get();
        $empleados = Empleado::with('sucursal')->
            whereDoesntHave('usuario')->orderBy('apellido_paterno')->get();

        return view('usuarios.create', compact('roles', 'empleados'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id' => ['nullable', 'exists:roles,id'],
            'empleado_id' => ['nullable', 'exists:empleados,id'],
        ]);

        User::create($validated);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function show(User $usuario): View
    {
        $usuario->load(['empleado', 'rol']);

        return view('usuarios.show', compact('usuario'));
    }

    public function edit(User $usuario): View
    {
        $roles = Rol::orderBy('nombre')->get();
        $empleados = Empleado::with('sucursal')->orderBy('apellido_paterno')->get();

        return view('usuarios.edit', compact('usuario', 'roles', 'empleados'));
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
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

        $usuario->update($validated);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario): RedirectResponse
    {
        $usuario->delete();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }
}
