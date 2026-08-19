<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RolController extends Controller
{
    public function index(Request $request): View
    {
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
        return view('roles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:roles,slug'],
        ]);

        Rol::create([
            'nombre' => $validated['nombre'],
            'slug' => $validated['slug'] ?: Str::slug($validated['nombre']),
        ]);

        return redirect()->route('roles.index')
            ->with('success', 'Rol creado correctamente.');
    }

    public function show(Rol $rol): View
    {
        $rol->load('usuarios');

        return view('roles.show', compact('rol'));
    }

    public function edit(Rol $rol): View
    {
        return view('roles.edit', compact('rol'));
    }

    public function update(Request $request, Rol $rol): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:roles,slug,'.$rol->id],
        ]);

        $rol->update([
            'nombre' => $validated['nombre'],
            'slug' => $validated['slug'] ?: Str::slug($validated['nombre']),
        ]);

        return redirect()->route('roles.index')
            ->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Rol $rol): RedirectResponse
    {
        $rol->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Rol eliminado correctamente.');
    }
}
