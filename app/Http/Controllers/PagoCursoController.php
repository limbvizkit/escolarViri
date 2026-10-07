<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Curso;
use App\Models\CursoAlumno;
use App\Models\Pago;
use App\Models\PagoCurso;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PagoCursoController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->filteredQuery($request);

        $pagos = $this->paginateOrdered($query, $request, $this->allowedSorts(), 'id');

        $filtros = [
            ['name' => 'curso_id', 'label' => 'Curso', 'options' => Curso::orderBy('nombre')->pluck('nombre', 'id')->all()],
            ['name' => 'mes', 'label' => 'Mes', 'options' => $this->mesesDisponibles()],
        ];

        return view('pagos-cursos.index', compact('pagos', 'filtros'));
    }

    public function create(): View
    {
        return view('pagos-cursos.create', $this->datosFormulario());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->reglas($request), $this->mensajes());

        DB::transaction(function () use ($validated) {
            $pagoCurso = PagoCurso::create($validated);

            $this->recalcularPagoDelMes((int) $pagoCurso->alumno_id, $pagoCurso->mes);
        });

        return redirect()->route('pagos-cursos.index')
            ->with('success', 'Pago de curso registrado correctamente.');
    }

    public function edit(PagoCurso $pagoCurso): View
    {
        $this->authorize('update', $pagoCurso);

        return view('pagos-cursos.edit', array_merge(
            $this->datosFormulario(),
            compact('pagoCurso'),
        ));
    }

    public function update(Request $request, PagoCurso $pagoCurso): RedirectResponse
    {
        $this->authorize('update', $pagoCurso);

        $validated = $request->validate($this->reglas($request), $this->mensajes());

        $alumnoAnterior = (int) $pagoCurso->alumno_id;
        $mesAnterior = $pagoCurso->mes;

        DB::transaction(function () use ($pagoCurso, $validated, $alumnoAnterior, $mesAnterior) {
            $pagoCurso->update($validated);

            // Si cambió el alumno o el mes, hay que recalcular ambos periodos.
            $this->recalcularPagoDelMes($alumnoAnterior, $mesAnterior);
            $this->recalcularPagoDelMes((int) $pagoCurso->alumno_id, $pagoCurso->mes);
        });

        return redirect()->route('pagos-cursos.index')
            ->with('success', 'Pago de curso actualizado correctamente.');
    }

    public function destroy(PagoCurso $pagoCurso): RedirectResponse
    {
        $this->authorize('delete', $pagoCurso);

        $alumnoId = (int) $pagoCurso->alumno_id;
        $mes = $pagoCurso->mes;

        DB::transaction(function () use ($pagoCurso, $alumnoId, $mes) {
            $pagoCurso->delete();

            $this->recalcularPagoDelMes($alumnoId, $mes);
        });

        return redirect()->route('pagos-cursos.index')
            ->with('success', 'Pago de curso eliminado correctamente.');
    }

    /**
     * Sincroniza el campo `cursos` del pago mensual del alumno con la
     * sumatoria de todos sus pagos de cursos del mismo mes.
     *
     * Este módulo es la fuente de verdad del concepto "cursos": si el alumno
     * tiene pagos de varios cursos en el mes, el pago mensual refleja la suma.
     */
    private function recalcularPagoDelMes(int $alumnoId, string $mes): void
    {
        $total = (float) PagoCurso::query()
            ->where('alumno_id', $alumnoId)
            ->where('mes', $mes)
            ->sum('monto');

        $pago = Pago::query()
            ->where('alumno_id', $alumnoId)
            ->where('mes', $mes)
            ->first();

        if ($pago === null) {
            if ($total > 0) {
                Pago::create([
                    'alumno_id' => $alumnoId,
                    'mes' => $mes,
                    'cursos' => $total,
                ]);
            }

            return;
        }

        $pago->update(['cursos' => $total > 0 ? $total : null]);
    }

    /**
     * Datos compartidos por el formulario de alta y edición.
     *
     * @return array{alumnos: Collection, cursosPorAlumno: array}
     */
    private function datosFormulario(): array
    {
        $alumnos = Alumno::with('gradoEscolar')->orderBy('apellido_paterno')->get();

        // Mapa alumno_id => [{ id, nombre }, ...] con solo los cursos en los
        // que el alumno está inscrito, para limitar el listado del formulario.
        $cursosPorAlumno = CursoAlumno::query()
            ->join('cursos', 'cursos.id', '=', 'curso_alumno.curso_id')
            ->orderBy('cursos.nombre')
            ->get(['curso_alumno.alumno_id', 'cursos.id as curso_id', 'cursos.nombre'])
            ->groupBy('alumno_id')
            ->map(fn ($items) => $items
                ->map(fn ($item) => ['id' => $item->curso_id, 'nombre' => $item->nombre])
                ->values()
                ->all())
            ->all();

        return compact('alumnos', 'cursosPorAlumno');
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = PagoCurso::with(['alumno.gradoEscolar', 'curso']);

        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        if ($request->filled('curso_id')) {
            $query->where('pagos_cursos.curso_id', $request->input('curso_id'));
        }

        if ($request->filled('mes')) {
            $query->where('pagos_cursos.mes', $request->input('mes'));
        }

        return $query;
    }

    private function allowedSorts(): array
    {
        return ['id', 'alumno_id', 'curso_id', 'mes', 'monto', 'created_at'];
    }

    private function reglas(Request $request): array
    {
        return [
            'alumno_id' => ['required', 'exists:alumnos,id'],
            'curso_id' => [
                'required',
                Rule::exists('curso_alumno', 'curso_id')
                    ->where(fn ($query) => $query->where('alumno_id', $request->input('alumno_id'))),
            ],
            'mes' => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function mensajes(): array
    {
        return [
            'alumno_id.required' => 'Selecciona un alumno.',
            'alumno_id.exists' => 'El alumno seleccionado no es válido.',
            'curso_id.required' => 'Selecciona un curso.',
            'curso_id.exists' => 'El curso seleccionado no está entre los cursos inscritos del alumno.',
            'mes.required' => 'Indica el mes y año del pago.',
            'mes.regex' => 'El formato del mes es inválido.',
            'monto.required' => 'Indica el monto pagado.',
            'monto.numeric' => 'El monto debe ser un número.',
            'monto.min' => 'El monto debe ser mayor a cero.',
            'observaciones.max' => 'Las observaciones no pueden superar los 1000 caracteres.',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function mesesDisponibles(): array
    {
        $meses = PagoCurso::query()->distinct()->orderByDesc('mes')->pluck('mes')->all();

        $opciones = [];
        foreach ($meses as $mes) {
            $opciones[$mes] = Pago::mesLabel($mes);
        }

        return $opciones;
    }
}
