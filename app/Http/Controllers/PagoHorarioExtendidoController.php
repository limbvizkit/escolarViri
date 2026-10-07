<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Estatus;
use App\Models\HorarioExtendido;
use App\Models\Pago;
use App\Models\PagoHorarioExtendido;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PagoHorarioExtendidoController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->filteredQuery($request);

        $pagos = $this->paginateOrdered($query, $request, $this->allowedSorts(), 'id');

        $filtros = [
            ['name' => 'horario_extendido_id', 'label' => 'Horario extendido', 'options' => $this->horariosDisponibles()],
            ['name' => 'mes', 'label' => 'Mes', 'options' => $this->mesesDisponibles()],
        ];

        return view('pagos-horarios-extendidos.index', compact('pagos', 'filtros'));
    }

    public function create(): View
    {
        return view('pagos-horarios-extendidos.create', [
            'alumnos' => Alumno::with('gradoEscolar')->orderBy('apellido_paterno')->get(),
            'horariosExtendidos' => HorarioExtendido::active()->orderBy('nombre')->get(),
            'registrosExistentes' => $this->registrosExistentes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->reglas(), $this->mensajes());

        DB::transaction(function () use ($validated) {
            $pago = PagoHorarioExtendido::create($validated);

            $this->recalcularPagoDelMes((int) $pago->alumno_id, $pago->mes);
        });

        return redirect()->route('pagos-horarios-extendidos.index')
            ->with('success', 'Pago de horario extendido registrado correctamente.');
    }

    public function edit(PagoHorarioExtendido $pagoHorarioExtendido): View
    {
        $this->authorize('update', $pagoHorarioExtendido);

        return view('pagos-horarios-extendidos.edit', [
            'pagoHorarioExtendido' => $pagoHorarioExtendido,
            'alumnos' => Alumno::with('gradoEscolar')->orderBy('apellido_paterno')->get(),
            'horariosExtendidos' => HorarioExtendido::active()->orderBy('nombre')->get(),
            'registrosExistentes' => [],
        ]);
    }

    public function update(Request $request, PagoHorarioExtendido $pagoHorarioExtendido): RedirectResponse
    {
        $this->authorize('update', $pagoHorarioExtendido);

        $validated = $request->validate($this->reglas(), $this->mensajes());

        $alumnoAnterior = (int) $pagoHorarioExtendido->alumno_id;
        $mesAnterior = $pagoHorarioExtendido->mes;

        DB::transaction(function () use ($pagoHorarioExtendido, $validated, $alumnoAnterior, $mesAnterior) {
            $pagoHorarioExtendido->update($validated);

            // Si cambió el alumno o el mes, hay que recalcular ambos periodos.
            $this->recalcularPagoDelMes($alumnoAnterior, $mesAnterior);
            $this->recalcularPagoDelMes((int) $pagoHorarioExtendido->alumno_id, $pagoHorarioExtendido->mes);
        });

        return redirect()->route('pagos-horarios-extendidos.index')
            ->with('success', 'Pago de horario extendido actualizado correctamente.');
    }

    public function destroy(PagoHorarioExtendido $pagoHorarioExtendido): RedirectResponse
    {
        $this->authorize('delete', $pagoHorarioExtendido);

        $alumnoId = (int) $pagoHorarioExtendido->alumno_id;
        $mes = $pagoHorarioExtendido->mes;

        DB::transaction(function () use ($pagoHorarioExtendido, $alumnoId, $mes) {
            $pagoHorarioExtendido->delete();

            $this->recalcularPagoDelMes($alumnoId, $mes);
        });

        return redirect()->route('pagos-horarios-extendidos.index')
            ->with('success', 'Pago de horario extendido eliminado correctamente.');
    }

    /**
     * Sincroniza el campo `horario_extendido` del pago mensual del alumno con
     * la sumatoria de todos sus pagos de horario extendido del mismo mes.
     */
    private function recalcularPagoDelMes(int $alumnoId, string $mes): void
    {
        $total = (float) PagoHorarioExtendido::query()
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
                    'horario_extendido' => $total,
                ]);
            }

            return;
        }

        $pago->update(['horario_extendido' => $total > 0 ? $total : null]);
    }

    /**
     * Pares alumno|mes que ya tienen al menos un registro, para avisar en el
     * formulario cuando se intenta registrar un mes repetido.
     *
     * @return array<int, string>
     */
    private function registrosExistentes(): array
    {
        return PagoHorarioExtendido::query()
            ->select('alumno_id', 'mes')
            ->get()
            ->map(fn ($pago) => $pago->alumno_id.'|'.$pago->mes)
            ->unique()
            ->values()
            ->all();
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = PagoHorarioExtendido::with(['alumno.gradoEscolar', 'horarioExtendido']);

        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        if ($request->filled('horario_extendido_id')) {
            $query->where('pagos_horarios_extendidos.horario_extendido_id', $request->input('horario_extendido_id'));
        }

        if ($request->filled('mes')) {
            $query->where('pagos_horarios_extendidos.mes', $request->input('mes'));
        }

        return $query;
    }

    private function allowedSorts(): array
    {
        return ['id', 'alumno_id', 'horario_extendido_id', 'mes', 'monto', 'created_at'];
    }

    private function reglas(): array
    {
        return [
            'alumno_id' => ['required', 'exists:alumnos,id'],
            'horario_extendido_id' => [
                'required',
                Rule::exists('horarios_extendidos', 'id')->where('estatus_id', Estatus::ACTIVO),
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
            'horario_extendido_id.required' => 'Selecciona un horario extendido.',
            'horario_extendido_id.exists' => 'El horario extendido seleccionado no es válido.',
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
    private function horariosDisponibles(): array
    {
        return HorarioExtendido::active()->orderBy('nombre')->pluck('nombre', 'id')->all();
    }

    /**
     * @return array<string, string>
     */
    private function mesesDisponibles(): array
    {
        $meses = PagoHorarioExtendido::query()->distinct()->orderByDesc('mes')->pluck('mes')->all();

        $opciones = [];
        foreach ($meses as $mes) {
            $opciones[$mes] = Pago::mesLabel($mes);
        }

        return $opciones;
    }
}
