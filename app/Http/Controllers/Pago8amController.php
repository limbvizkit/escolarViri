<?php

namespace App\Http\Controllers;

use App\Exports\Pago8amExport;
use App\Models\Alumno;
use App\Models\Pago;
use App\Models\Pago8am;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class Pago8amController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->filteredQuery($request);

        $pagos = $this->paginateOrdered($query, $request, $this->allowedSorts(), 'id');

        $filtros = [
            ['name' => 'mes', 'label' => 'Mes', 'options' => $this->mesesDisponibles()],
        ];

        return view('pagos-8am.index', compact('pagos', 'filtros'));
    }

    public function exportPdf(Request $request)
    {
        $pagos = $this->filteredQuery($request)
            ->orderBy($this->sortField($request, $this->allowedSorts(), 'id'), $this->sortDirection($request))
            ->get();

        $pdf = Pdf::loadView('pagos-8am.pdf', compact('pagos'))->setPaper('a4', 'landscape');

        return $pdf->download('pagos-8am-'.now()->format('Y-m-d').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $query = $this->filteredQuery($request)
            ->orderBy($this->sortField($request, $this->allowedSorts(), 'id'), $this->sortDirection($request));

        return Excel::download(new Pago8amExport($query), 'pagos-8am-'.now()->format('Y-m-d').'.xlsx');
    }

    public function create(): View
    {
        return view('pagos-8am.create', [
            'alumnos' => Alumno::with('gradoEscolar')->orderBy('apellido_paterno')->get(),
            'registrosExistentes' => $this->registrosExistentes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->reglas(), $this->mensajes());

        DB::transaction(function () use ($validated) {
            $pago8am = Pago8am::create($validated);

            $this->recalcularPagoDelMes((int) $pago8am->alumno_id, $pago8am->mes);
        });

        return redirect()->route('pagos-8am.index')
            ->with('success', 'Pago de entrada 8 AM registrado correctamente.');
    }

    public function edit(Pago8am $pago8am): View
    {
        $this->authorize('update', $pago8am);

        return view('pagos-8am.edit', [
            'pago8am' => $pago8am,
            'alumnos' => Alumno::with('gradoEscolar')->orderBy('apellido_paterno')->get(),
            'registrosExistentes' => [],
        ]);
    }

    public function update(Request $request, Pago8am $pago8am): RedirectResponse
    {
        $this->authorize('update', $pago8am);

        $validated = $request->validate($this->reglas(), $this->mensajes());

        $alumnoAnterior = (int) $pago8am->alumno_id;
        $mesAnterior = $pago8am->mes;

        DB::transaction(function () use ($pago8am, $validated, $alumnoAnterior, $mesAnterior) {
            $pago8am->update($validated);

            // Si cambió el alumno o el mes, hay que recalcular ambos periodos.
            $this->recalcularPagoDelMes($alumnoAnterior, $mesAnterior);
            $this->recalcularPagoDelMes((int) $pago8am->alumno_id, $pago8am->mes);
        });

        return redirect()->route('pagos-8am.index')
            ->with('success', 'Pago de entrada 8 AM actualizado correctamente.');
    }

    public function destroy(Pago8am $pago8am): RedirectResponse
    {
        $this->authorize('delete', $pago8am);

        $alumnoId = (int) $pago8am->alumno_id;
        $mes = $pago8am->mes;

        DB::transaction(function () use ($pago8am, $alumnoId, $mes) {
            $pago8am->delete();

            $this->recalcularPagoDelMes($alumnoId, $mes);
        });

        return redirect()->route('pagos-8am.index')
            ->with('success', 'Pago de entrada 8 AM eliminado correctamente.');
    }

    /**
     * Sincroniza el campo `entrada_8am` del pago mensual del alumno con la
     * sumatoria de todos sus pagos de entrada 8 AM del mismo mes.
     */
    private function recalcularPagoDelMes(int $alumnoId, string $mes): void
    {
        $total = (float) Pago8am::query()
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
                    'entrada_8am' => $total,
                ]);
            }

            return;
        }

        $pago->update(['entrada_8am' => $total > 0 ? $total : null]);
    }

    /**
     * Pares alumno|mes que ya tienen al menos un registro, para avisar en el
     * formulario cuando se intenta registrar un mes repetido.
     *
     * @return array<int, string>
     */
    private function registrosExistentes(): array
    {
        return Pago8am::query()
            ->select('alumno_id', 'mes')
            ->get()
            ->map(fn ($pago) => $pago->alumno_id.'|'.$pago->mes)
            ->unique()
            ->values()
            ->all();
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = Pago8am::with(['alumno.gradoEscolar']);

        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        if ($request->filled('mes')) {
            $query->where('pagos_8am.mes', $request->input('mes'));
        }

        return $query;
    }

    private function allowedSorts(): array
    {
        return ['id', 'alumno_id', 'mes', 'monto', 'created_at'];
    }

    private function reglas(): array
    {
        return [
            'alumno_id' => ['required', 'exists:alumnos,id'],
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
        $meses = Pago8am::query()->distinct()->orderByDesc('mes')->pluck('mes')->all();

        $opciones = [];
        foreach ($meses as $mes) {
            $opciones[$mes] = Pago::mesLabel($mes);
        }

        return $opciones;
    }
}
