<?php

namespace App\Http\Controllers;

use App\Models\GradoEscolar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GradoEscolarController extends Controller
{
    public function index(Request $request): View
    {
        $query = GradoEscolar::withCount('alumnos');

        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        if ($request->filled('estatus')) {
            $query->where('grados_escolares.estatus', $request->boolean('estatus'));
        }

        $gradosEscolares = $this->paginateOrdered(
            $query,
            $request,
            ['id', 'nombre', 'alumnos_count', 'estatus'],
            'nombre',
        );

        $filtros = [
            ['name' => 'estatus', 'label' => 'Estatus', 'options' => ['1' => 'Activo', '0' => 'Inactivo']],
        ];

        return view('grados-escolares.index', compact('gradosEscolares', 'filtros'));
    }

    public function create(): View
    {
        return view('grados-escolares.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->reglas());

        GradoEscolar::create([
            'nombre' => $validated['nombre'],
            'slug' => Str::slug($validated['nombre']),
            'estatus' => $request->boolean('estatus'),
        ]);

        return redirect()->route('grados-escolares.index')
            ->with('success', 'Grado Escolar creado correctamente.');
    }

    public function show(GradoEscolar $gradoEscolar): View
    {
        $gradoEscolar->load(['alumnos' => fn ($q) => $q->orderBy('apellido_paterno')]);

        return view('grados-escolares.show', compact('gradoEscolar'));
    }

    public function edit(GradoEscolar $gradoEscolar): View
    {
        return view('grados-escolares.edit', compact('gradoEscolar'));
    }

    public function update(Request $request, GradoEscolar $gradoEscolar): RedirectResponse
    {
        $validated = $request->validate($this->reglas());

        $gradoEscolar->update([
            'nombre' => $validated['nombre'],
            'slug' => Str::slug($validated['nombre']),
            'estatus' => $request->boolean('estatus'),
        ]);

        return redirect()->route('grados-escolares.index')
            ->with('success', 'Grado Escolar actualizado correctamente.');
    }

    public function destroy(GradoEscolar $gradoEscolar): RedirectResponse
    {
        if ($gradoEscolar->alumnos()->exists()) {
            return redirect()->route('grados-escolares.index')
                ->with('error', 'No se puede eliminar un grado escolar que tiene alumnos asignados.');
        }

        $gradoEscolar->delete();

        return redirect()->route('grados-escolares.index')
            ->with('success', 'Grado Escolar eliminado correctamente.');
    }

    private function reglas(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
        ];
    }
}
