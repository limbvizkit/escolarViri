<?php

namespace App\Http\Controllers;

use App\Exports\PagoTallerExport;
use App\Models\Alumno;
use App\Models\Pago;
use App\Models\PagoTaller;
use App\Models\Taller;
use App\Models\TallerAlumno;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class PagoTallerController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->filteredQuery($request);

        $pagos = $this->paginateOrdered($query, $request, $this->allowedSorts(), 'id');

        $filtros = [
            ['name' => 'taller_id', 'label' => 'Taller', 'options' => Taller::orderBy('nombre')->pluck('nombre', 'id')->all()],
            ['name' => 'mes', 'label' => 'Mes', 'options' => $this->mesesDisponibles()],
        ];

        return view('pagos-talleres.index', compact('pagos', 'filtros'));
    }

    public function exportPdf(Request $request)
    {
        $pagos = $this->filteredQuery($request)
            ->orderBy($this->sortField($request, $this->allowedSorts(), 'id'), $this->sortDirection($request))
            ->get();

        $pdf = Pdf::loadView('pagos-talleres.pdf', compact('pagos'))->setPaper('a4', 'landscape');

        return $pdf->download('pagos-talleres-'.now()->format('Y-m-d').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $query = $this->filteredQuery($request)
            ->orderBy($this->sortField($request, $this->allowedSorts(), 'id'), $this->sortDirection($request));

        return Excel::download(new PagoTallerExport($query), 'pagos-talleres-'.now()->format('Y-m-d').'.xlsx');
    }

    public function create(): View
    {
        return view('pagos-talleres.create', $this->datosFormulario());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->reglas($request), $this->mensajes());

        DB::transaction(function () use ($validated) {
            $pagoTaller = PagoTaller::create($validated);

            $this->recalcularPagoDelMes((int) $pagoTaller->alumno_id, $pagoTaller->mes);
        });

        return redirect()->route('pagos-talleres.index')
            ->with('success', 'Pago de taller registrado correctamente.');
    }

    public function edit(PagoTaller $pagoTaller): View
    {
        $this->authorize('update', $pagoTaller);

        return view('pagos-talleres.edit', array_merge(
            $this->datosFormulario(),
            compact('pagoTaller'),
        ));
    }

    public function update(Request $request, PagoTaller $pagoTaller): RedirectResponse
    {
        $this->authorize('update', $pagoTaller);

        $validated = $request->validate($this->reglas($request), $this->mensajes());

        $alumnoAnterior = (int) $pagoTaller->alumno_id;
        $mesAnterior = $pagoTaller->mes;

        DB::transaction(function () use ($pagoTaller, $validated, $alumnoAnterior, $mesAnterior) {
            $pagoTaller->update($validated);

            // Si cambió el alumno o el mes, hay que recalcular ambos periodos.
            $this->recalcularPagoDelMes($alumnoAnterior, $mesAnterior);
            $this->recalcularPagoDelMes((int) $pagoTaller->alumno_id, $pagoTaller->mes);
        });

        return redirect()->route('pagos-talleres.index')
            ->with('success', 'Pago de taller actualizado correctamente.');
    }

    public function destroy(PagoTaller $pagoTaller): RedirectResponse
    {
        $this->authorize('delete', $pagoTaller);

        $alumnoId = (int) $pagoTaller->alumno_id;
        $mes = $pagoTaller->mes;

        DB::transaction(function () use ($pagoTaller, $alumnoId, $mes) {
            $pagoTaller->delete();

            $this->recalcularPagoDelMes($alumnoId, $mes);
        });

        return redirect()->route('pagos-talleres.index')
            ->with('success', 'Pago de taller eliminado correctamente.');
    }

    /**
     * Sincroniza el campo `talleres` del pago mensual del alumno con la
     * sumatoria de todos sus pagos de talleres del mismo mes.
     *
     * Este módulo es la fuente de verdad del concepto "talleres": si el alumno
     * tiene pagos de varios talleres en el mes, el pago mensual refleja la suma.
     */
    private function recalcularPagoDelMes(int $alumnoId, string $mes): void
    {
        $total = (float) PagoTaller::query()
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
                    'talleres' => $total,
                ]);
            }

            return;
        }

        $pago->update(['talleres' => $total > 0 ? $total : null]);
    }

    /**
     * Datos compartidos por el formulario de alta y edición.
     *
     * @return array{alumnos: Collection, talleresPorAlumno: array}
     */
    private function datosFormulario(): array
    {
        $alumnos = Alumno::with('gradoEscolar')->orderBy('apellido_paterno')->get();

        // Mapa alumno_id => [{ id, nombre }, ...] con solo los talleres en los
        // que el alumno está inscrito, para limitar el listado del formulario.
        $talleresPorAlumno = TallerAlumno::query()
            ->join('talleres', 'talleres.id', '=', 'taller_alumno.taller_id')
            ->orderBy('talleres.nombre')
            ->get(['taller_alumno.alumno_id', 'talleres.id as taller_id', 'talleres.nombre'])
            ->groupBy('alumno_id')
            ->map(fn ($items) => $items
                ->map(fn ($item) => ['id' => $item->taller_id, 'nombre' => $item->nombre])
                ->values()
                ->all())
            ->all();

        return compact('alumnos', 'talleresPorAlumno');
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = PagoTaller::with(['alumno.gradoEscolar', 'taller']);

        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        if ($request->filled('taller_id')) {
            $query->where('pagos_talleres.taller_id', $request->input('taller_id'));
        }

        if ($request->filled('mes')) {
            $query->where('pagos_talleres.mes', $request->input('mes'));
        }

        return $query;
    }

    private function allowedSorts(): array
    {
        return ['id', 'alumno_id', 'taller_id', 'mes', 'monto', 'created_at'];
    }

    private function reglas(Request $request): array
    {
        return [
            'alumno_id' => ['required', 'exists:alumnos,id'],
            'taller_id' => [
                'required',
                Rule::exists('taller_alumno', 'taller_id')
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
            'taller_id.required' => 'Selecciona un taller.',
            'taller_id.exists' => 'El taller seleccionado no está entre los talleres inscritos del alumno.',
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
        $meses = PagoTaller::query()->distinct()->orderByDesc('mes')->pluck('mes')->all();

        $opciones = [];
        foreach ($meses as $mes) {
            $opciones[$mes] = Pago::mesLabel($mes);
        }

        return $opciones;
    }
}
