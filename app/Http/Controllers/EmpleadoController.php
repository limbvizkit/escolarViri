<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\Sucursal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmpleadoController extends Controller
{
    public function index(Request $request): View
    {
        $query = Empleado::with('sucursal');

        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        if ($request->filled('estatus')) {
            $query->where('empleados.estatus_id', $request->input('estatus'));
        }

        if ($request->filled('sucursal_id')) {
            $query->where('empleados.sucursal_id', $request->input('sucursal_id'));
        }

        $empleados = $this->paginateOrdered(
            $query,
            $request,
            ['id', 'nombre', 'apellido_paterno', 'apellido_materno', 'email', 'puesto', 'estatus_id'],
            'apellido_paterno',
        );

        $filtros = [
            ['name' => 'sucursal_id', 'label' => 'Sucursal', 'options' => Sucursal::orderBy('nombre')->pluck('nombre', 'id')->all()],
            ['name' => 'estatus', 'label' => 'Estatus', 'options' => [Estatus::ACTIVO => 'Activo', Estatus::INACTIVO => 'Inactivo']],
        ];

        return view('empleados.index', compact('empleados', 'filtros'));
    }

    public function create(): View
    {
        $sucursales = Sucursal::with('escuela')->active()->orderBy('nombre')->get();

        return view('empleados.create', compact('sucursales'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sucursal_id' => ['required', 'exists:sucursales,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'puesto' => ['nullable', 'string', 'max:255'],
            'estatus_id' => ['nullable', 'exists:estatus,id'],
        ]);

        Empleado::create($validated + ['estatus_id' => (int) $request->input('estatus_id', Estatus::ACTIVO)]);

        return redirect()->route('empleados.index')
            ->with('success', 'Empleado creado correctamente.');
    }

    public function show(Empleado $empleado): View
    {
        $empleado->load(['sucursal', 'usuario']);

        return view('empleados.show', compact('empleado'));
    }

    public function edit(Empleado $empleado): View
    {
        $sucursales = Sucursal::with('escuela')->active()->orderBy('nombre')->get();

        return view('empleados.edit', compact('empleado', 'sucursales'));
    }

    public function update(Request $request, Empleado $empleado): RedirectResponse
    {
        $validated = $request->validate([
            'sucursal_id' => ['required', 'exists:sucursales,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'puesto' => ['nullable', 'string', 'max:255'],
            'estatus_id' => ['nullable', 'exists:estatus,id'],
        ]);

        $empleado->update($validated + ['estatus_id' => (int) $request->input('estatus_id', Estatus::ACTIVO)]);

        return redirect()->route('empleados.index')
            ->with('success', 'Empleado actualizado correctamente.');
    }

    public function destroy(Empleado $empleado): RedirectResponse
    {
        $empleado->update(['estatus_id' => Estatus::ELIMINADO]);

        return redirect()->route('empleados.index')
            ->with('success', 'Empleado eliminado correctamente.');
    }
}
