<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Taller;
use App\Models\TallerAlumno;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TallerController extends Controller
{
    public function index(): View
    {
        $talleres = Taller::orderBy('nombre')->get();

        $inscripciones = TallerAlumno::with(['alumno.gradoEscolar', 'taller'])
            ->orderBy('hora_inicio')
            ->get();

        return view('talleres.index', compact('talleres', 'inscripciones'));
    }

    public function create(): View
    {
        return view('talleres.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->reglas(), $this->mensajes());

        Taller::create($validated);

        return redirect()->route('talleres.index')
            ->with('success', 'Taller creado correctamente.');
    }

    public function edit(Taller $taller): View
    {
        return view('talleres.edit', compact('taller'));
    }

    public function update(Request $request, Taller $taller): RedirectResponse
    {
        $validated = $request->validate($this->reglas(), $this->mensajes());

        $taller->update($validated);

        return redirect()->route('talleres.index')
            ->with('success', 'Taller actualizado correctamente.');
    }

    public function destroy(Taller $taller): RedirectResponse
    {
        $taller->delete();

        return redirect()->route('talleres.index')
            ->with('success', 'Taller eliminado correctamente.');
    }

    public function alumnoCreate(Taller $taller): View
    {
        $alumnosDisponibles = Alumno::active()
            ->orderBy('apellido_paterno')
            ->orderBy('nombre')
            ->with('gradoEscolar')
            ->whereNotIn('id', $taller->alumnos()->pluck('alumnos.id'))
            ->get();

        return view('talleres.alumnos-create', compact('taller', 'alumnosDisponibles'));
    }

    public function alumnoStore(Request $request, Taller $taller): RedirectResponse
    {
        $validated = $request->validate($this->reglasAlumno(), $this->mensajesAlumno());

        try {
            TallerAlumno::create([
                'taller_id' => $taller->id,
                'alumno_id' => $validated['alumno_id'],
                'hora_inicio' => $validated['hora_inicio'],
                'hora_fin' => $validated['hora_fin'],
                'monto_pagado' => $validated['monto_pagado'] ?? null,
            ]);
        } catch (QueryException) {
            return redirect()->route('talleres.alumnos.create', $taller)
                ->with('error', 'Ese alumno ya está inscrito en este taller.');
        }

        return redirect()->route('talleres.index')
            ->with('success', 'Alumno agregado al taller correctamente.');
    }

    public function alumnoDestroy(Taller $taller, Alumno $alumno): RedirectResponse
    {
        TallerAlumno::where('taller_id', $taller->id)
            ->where('alumno_id', $alumno->id)
            ->delete();

        return redirect()->route('talleres.index')
            ->with('success', 'Alumno quitado del taller correctamente.');
    }

    public function montoUpdate(Request $request, TallerAlumno $tallerAlumno)
    {
        $datos = $request->validate(['monto_pagado' => ['nullable', 'numeric', 'min:0']]);

        try {
            $tallerAlumno->update($datos);
        } catch (QueryException) {
            return response()->json([
                'mensaje' => 'No se pudo guardar el monto pagado.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'mensaje' => 'Cambios guardados.',
            'valor' => $tallerAlumno->fresh()->monto_pagado,
        ]);
    }

    private function reglas(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'costo' => ['required', 'numeric', 'min:0'],
        ];
    }

    private function mensajes(): array
    {
        return [
            'nombre.required' => 'El nombre del taller es obligatorio.',
            'costo.required' => 'El costo es obligatorio.',
        ];
    }

    private function reglasAlumno(): array
    {
        return [
            'alumno_id' => ['required', 'exists:alumnos,id'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'monto_pagado' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    private function mensajesAlumno(): array
    {
        return [
            'alumno_id.required' => 'Selecciona un alumno.',
            'hora_inicio.required' => 'Indica la hora de inicio.',
            'hora_fin.required' => 'Indica la hora de fin.',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ];
    }
}
