<?php

namespace App\Http\Controllers;

use App\Models\Escuela;
use App\Models\Sucursal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SucursalController extends Controller
{
    public function index(Request $request): View
    {
        $query = Sucursal::with('escuela')->withCount('empleados');

        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        if ($request->filled('estatus')) {
            $query->where('sucursales.estatus', $request->boolean('estatus'));
        }

        if ($request->filled('escuela_id')) {
            $query->where('sucursales.escuela_id', $request->input('escuela_id'));
        }

        $sucursales = $this->paginateOrdered(
            $query,
            $request,
            ['id', 'nombre', 'direccion', 'estatus', 'empleados_count'],
            'nombre',
        );

        $filtros = [
            ['name' => 'escuela_id', 'label' => 'Escuela', 'options' => Escuela::orderBy('nombre')->pluck('nombre', 'id')->all()],
            ['name' => 'estatus', 'label' => 'Estatus', 'options' => ['1' => 'Activa', '0' => 'Inactiva']],
        ];

        return view('sucursales.index', compact('sucursales', 'filtros'));
    }

    public function create(): View
    {
        $escuelas = Escuela::active()->orderBy('nombre')->get();

        return view('sucursales.create', compact('escuelas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'escuela_id' => ['required', 'exists:escuelas,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'estatus' => ['nullable', 'boolean'],
        ]);

        Sucursal::create($validated + ['estatus' => $request->boolean('estatus')]);

        return redirect()->route('sucursales.index')
            ->with('success', 'Sucursal creada correctamente.');
    }

    public function show(Sucursal $sucursal): View
    {
        $sucursal->load(['escuela', 'empleados']);

        return view('sucursales.show', compact('sucursal'));
    }

    public function edit(Sucursal $sucursal): View
    {
        $escuelas = Escuela::active()->orderBy('nombre')->get();

        return view('sucursales.edit', compact('sucursal', 'escuelas'));
    }

    public function update(Request $request, Sucursal $sucursal): RedirectResponse
    {
        $validated = $request->validate([
            'escuela_id' => ['required', 'exists:escuelas,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'estatus' => ['nullable', 'boolean'],
        ]);

        $sucursal->update($validated + ['estatus' => $request->boolean('estatus')]);

        return redirect()->route('sucursales.index')
            ->with('success', 'Sucursal actualizada correctamente.');
    }

    public function destroy(Sucursal $sucursal): RedirectResponse
    {
        $sucursal->delete();

        return redirect()->route('sucursales.index')
            ->with('success', 'Sucursal eliminada correctamente.');
    }
}
