<?php

namespace App\Http\Controllers;

use App\Exports\CursoAlumnoExport;
use App\Models\Alumno;
use App\Models\Curso;
use App\Models\CursoAlumno;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class CursoController extends Controller
{
    public function index(): View
    {
        $cursos = Curso::orderBy('nombre')->get();

        $inscripciones = $this->inscripcionesQuery()->get();

        return view('cursos.index', compact('cursos', 'inscripciones'));
    }

    public function exportPdf()
    {
        $inscripciones = $this->inscripcionesQuery()->get();

        $pdf = Pdf::loadView('cursos.pdf', compact('inscripciones'))->setPaper('a4', 'landscape');

        return $pdf->download('cursos-'.now()->format('Y-m-d').'.pdf');
    }

    public function exportExcel()
    {
        $query = $this->inscripcionesQuery();

        return Excel::download(new CursoAlumnoExport($query), 'cursos-'.now()->format('Y-m-d').'.xlsx');
    }

    public function create(): View
    {
        return view('cursos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->reglas($request), $this->mensajes());

        Curso::create($validated);

        return redirect()->route('cursos.index')
            ->with('success', 'Curso creado correctamente.');
    }

    public function edit(Curso $curso): View
    {
        return view('cursos.edit', compact('curso'));
    }

    public function update(Request $request, Curso $curso): RedirectResponse
    {
        $validated = $request->validate($this->reglas($request), $this->mensajes());

        $curso->update($validated);

        return redirect()->route('cursos.index')
            ->with('success', 'Curso actualizado correctamente.');
    }

    public function destroy(Curso $curso): RedirectResponse
    {
        $curso->delete();

        return redirect()->route('cursos.index')
            ->with('success', 'Curso eliminado correctamente.');
    }

    public function alumnoCreate(Curso $curso): View
    {
        $alumnosDisponibles = Alumno::active()
            ->orderBy('apellido_paterno')
            ->orderBy('nombre')
            ->with('gradoEscolar')
            ->whereNotIn('id', $curso->alumnos()->pluck('alumnos.id'))
            ->get();

        return view('cursos.alumnos-create', compact('curso', 'alumnosDisponibles'));
    }

    public function alumnoStore(Request $request, Curso $curso): RedirectResponse
    {
        $validated = $request->validate($this->reglasAlumno(), $this->mensajesAlumno());

        try {
            CursoAlumno::create([
                'curso_id' => $curso->id,
                'alumno_id' => $validated['alumno_id'],
            ]);
        } catch (QueryException) {
            return redirect()->route('cursos.alumnos.create', $curso)
                ->with('error', 'Ese alumno ya está inscrito en este curso.');
        }

        return redirect()->route('cursos.index')
            ->with('success', 'Alumno agregado al curso correctamente.');
    }

    public function alumnosStoreBulk(Request $request, Curso $curso): RedirectResponse
    {
        $alumnosDisponiblesIds = Alumno::active()
            ->whereNotIn('id', $curso->alumnos()->pluck('alumnos.id'))
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $seleccionados = array_filter(
            array_map('intval', (array) $request->input('seleccionados', [])),
            fn ($id) => $id > 0
        );

        $rules = [
            'seleccionados' => ['required', 'array', 'min:1'],
            'seleccionados.*' => ['integer', Rule::in($alumnosDisponiblesIds)],
        ];

        $validated = $request->validate($rules, $this->mensajesAlumnosBulk());

        try {
            DB::transaction(function () use ($curso, $validated) {
                foreach ($validated['seleccionados'] as $alumnoId) {
                    CursoAlumno::create([
                        'curso_id' => $curso->id,
                        'alumno_id' => $alumnoId,
                    ]);
                }
            });
        } catch (QueryException) {
            return redirect()->route('cursos.alumnos.create', $curso)
                ->with('error', 'No se pudieron guardar las inscripciones. Verifica que ningún alumno ya esté inscrito.');
        }

        return redirect()->route('cursos.index')
            ->with('success', 'Alumnos inscritos al curso correctamente.');
    }

    public function alumnoDestroy(Curso $curso, Alumno $alumno): RedirectResponse
    {
        CursoAlumno::where('curso_id', $curso->id)
            ->where('alumno_id', $alumno->id)
            ->delete();

        return redirect()->route('cursos.index')
            ->with('success', 'Alumno quitado del curso correctamente.');
    }

    private function inscripcionesQuery(): Builder
    {
        return CursoAlumno::with(['alumno.gradoEscolar', 'curso'])
            ->orderBy('curso_id');
    }

    private function reglas(Request $request): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'costo' => ['required', 'numeric', 'min:0'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => array_values(array_filter([
                'nullable',
                'date',
                $request->filled('fecha_inicio') ? 'after_or_equal:fecha_inicio' : null,
            ])),
            'hora_inicio' => ['nullable', 'date_format:H:i'],
            'hora_fin' => array_values(array_filter([
                'nullable',
                'date_format:H:i',
                $request->filled('hora_inicio') ? 'after:hora_inicio' : null,
            ])),
        ];
    }

    private function mensajes(): array
    {
        return [
            'nombre.required' => 'El nombre del curso es obligatorio.',
            'costo.required' => 'El costo es obligatorio.',
            'costo.numeric' => 'El costo debe ser un número.',
            'costo.min' => 'El costo no puede ser negativo.',
            'fecha_inicio.date' => 'La fecha de inicio no es válida.',
            'fecha_fin.date' => 'La fecha de fin no es válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'hora_inicio.date_format' => 'La hora de inicio no es válida.',
            'hora_fin.date_format' => 'La hora de fin no es válida.',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ];
    }

    private function reglasAlumno(): array
    {
        return [
            'alumno_id' => ['required', 'exists:alumnos,id'],
        ];
    }

    private function mensajesAlumno(): array
    {
        return [
            'alumno_id.required' => 'Selecciona un alumno.',
            'alumno_id.exists' => 'El alumno seleccionado no es válido.',
        ];
    }

    private function mensajesAlumnosBulk(): array
    {
        return [
            'seleccionados.required' => 'Selecciona al menos un alumno para inscribir.',
            'seleccionados.min' => 'Selecciona al menos un alumno para inscribir.',
            'seleccionados.*.in' => 'Uno de los alumnos seleccionados no está disponible para este curso.',
        ];
    }
}
