<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Pago;
use App\Models\PagoLunch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PagoLunchController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->filteredQuery($request);

        $pagos = $this->paginateOrdered($query, $request, $this->allowedSorts(), 'id');

        $filtros = [
            ['name' => 'mes', 'label' => 'Mes', 'options' => $this->mesesDisponibles()],
        ];

        return view('pagos-lunch.index', compact('pagos', 'filtros'));
    }

    public function create(): View
    {
        return view('pagos-lunch.create', [
            'alumnos' => Alumno::with('gradoEscolar')->orderBy('apellido_paterno')->get(),
            'registrosExistentes' => $this->registrosExistentes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->reglas(), $this->mensajes());

        DB::transaction(function () use ($validated) {
            $pagoLunch = PagoLunch::create($validated);

            $this->recalcularPagoDelMes((int) $pagoLunch->alumno_id, $pagoLunch->mes);
        });

        return redirect()->route('pagos-lunch.index')
            ->with('success', 'Pago de lunch registrado correctamente.');
    }

    public function edit(PagoLunch $pagoLunch): View
    {
        $this->authorize('update', $pagoLunch);

        return view('pagos-lunch.edit', [
            'pagoLunch' => $pagoLunch,
            'alumnos' => Alumno::with('gradoEscolar')->orderBy('apellido_paterno')->get(),
            'registrosExistentes' => [],
        ]);
    }

    public function update(Request $request, PagoLunch $pagoLunch): RedirectResponse
    {
        $this->authorize('update', $pagoLunch);

        $validated = $request->validate($this->reglas(), $this->mensajes());

        $alumnoAnterior = (int) $pagoLunch->alumno_id;
        $mesAnterior = $pagoLunch->mes;

        DB::transaction(function () use ($pagoLunch, $validated, $alumnoAnterior, $mesAnterior) {
            $pagoLunch->update($validated);

            // Si cambió el alumno o el mes, hay que recalcular ambos periodos.
            $this->recalcularPagoDelMes($alumnoAnterior, $mesAnterior);
            $this->recalcularPagoDelMes((int) $pagoLunch->alumno_id, $pagoLunch->mes);
        });

        return redirect()->route('pagos-lunch.index')
            ->with('success', 'Pago de lunch actualizado correctamente.');
    }

    public function destroy(PagoLunch $pagoLunch): RedirectResponse
    {
        $this->authorize('delete', $pagoLunch);

        $alumnoId = (int) $pagoLunch->alumno_id;
        $mes = $pagoLunch->mes;

        DB::transaction(function () use ($pagoLunch, $alumnoId, $mes) {
            $pagoLunch->delete();

            $this->recalcularPagoDelMes($alumnoId, $mes);
        });

        return redirect()->route('pagos-lunch.index')
            ->with('success', 'Pago de lunch eliminado correctamente.');
    }

    /**
     * Sincroniza el campo `lunch` del pago mensual del alumno con la
     * sumatoria de todos sus pagos de lunch del mismo mes.
     */
    private function recalcularPagoDelMes(int $alumnoId, string $mes): void
    {
        $total = (float) PagoLunch::query()
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
                    'lunch' => $total,
                ]);
            }

            return;
        }

        $pago->update(['lunch' => $total > 0 ? $total : null]);
    }

    /**
     * Pares alumno|mes que ya tienen al menos un registro, para avisar en el
     * formulario cuando se intenta registrar un mes repetido.
     *
     * @return array<int, string>
     */
    private function registrosExistentes(): array
    {
        return PagoLunch::query()
            ->select('alumno_id', 'mes')
            ->get()
            ->map(fn ($pago) => $pago->alumno_id.'|'.$pago->mes)
            ->unique()
            ->values()
            ->all();
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = PagoLunch::with(['alumno.gradoEscolar']);

        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        if ($request->filled('mes')) {
            $query->where('pagos_lunch.mes', $request->input('mes'));
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
        $meses = PagoLunch::query()->distinct()->orderByDesc('mes')->pluck('mes')->all();

        $opciones = [];
        foreach ($meses as $mes) {
            $opciones[$mes] = Pago::mesLabel($mes);
        }

        return $opciones;
    }
}
