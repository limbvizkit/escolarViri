<?php

namespace App\Http\Controllers;

use App\Models\Escuela;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EscuelaController extends Controller
{
    public function index(Request $request): View
    {
        $query = Escuela::withCount('sucursales');

        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->boolean('estatus'));
        }

        $escuelas = $this->paginateOrdered(
            $query,
            $request,
            ['id', 'clave', 'nombre', 'direccion', 'estatus', 'sucursales_count'],
            'nombre',
        );

        $filtros = [
            ['name' => 'estatus', 'label' => 'Estatus', 'options' => ['1' => 'Activa', '0' => 'Inactiva']],
        ];

        return view('escuelas.index', compact('escuelas', 'filtros'));
    }

    public function create(): View
    {
        return view('escuelas.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'clave' => ['required', 'string', 'max:50', 'unique:escuelas,clave'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'estatus' => ['nullable', 'boolean'],
        ]);

        Escuela::create($validated + ['estatus' => $request->boolean('estatus')]);

        return redirect()->route('escuelas.index')
            ->with('success', 'Escuela creada correctamente.');
    }

    public function show(Escuela $escuela): View
    {
        $escuela->load(['sucursales' => fn ($q) => $q->withCount('empleados')]);

        return view('escuelas.show', compact('escuela'));
    }

    public function edit(Escuela $escuela): View
    {
        return view('escuelas.edit', compact('escuela'));
    }

    public function update(Request $request, Escuela $escuela): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'clave' => ['required', 'string', 'max:50', 'unique:escuelas,clave,'.$escuela->id],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'estatus' => ['nullable', 'boolean'],
        ]);

        $escuela->update($validated + ['estatus' => $request->boolean('estatus')]);

        return redirect()->route('escuelas.index')
            ->with('success', 'Escuela actualizada correctamente.');
    }

    public function destroy(Escuela $escuela): RedirectResponse
    {
        $escuela->delete();

        return redirect()->route('escuelas.index')
            ->with('success', 'Escuela eliminada correctamente.');
    }
}
