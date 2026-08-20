<?php

namespace App\Http\Controllers;

use App\Models\Adeudo;
use App\Models\AdeudoAbono;
use App\Models\Alumno;
use App\Models\FormaPago;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdeudoController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->filteredQuery($request);

        $adeudos = $this->paginateOrdered(
            $query,
            $request,
            ['id', 'alumno_id', 'concepto', 'monto', 'monto_pagado', 'estatus'],
            'id',
        );

        $filtros = [
            ['name' => 'estatus', 'label' => 'Estatus', 'options' => [
                'pendiente' => 'Pendiente',
                'parcial' => 'Parcial',
                'pagado' => 'Pagado',
            ]],
        ];

        return view('adeudos.index', compact('adeudos', 'filtros'));
    }

    public function create(): View
    {
        $alumnos = Alumno::with('gradoEscolar')->orderBy('apellido_paterno')->get();

        return view('adeudos.create', compact('alumnos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->reglas(), $this->mensajes());

        Adeudo::create($validated + ['monto_pagado' => 0]);

        return redirect()->route('adeudos.index')
            ->with('success', 'Adeudo registrado correctamente.');
    }

    public function show(Adeudo $adeudo): View
    {
        $adeudo->load([
            'alumno.gradoEscolar',
            'abonos' => fn ($q) => $q->orderByDesc('fecha')->orderByDesc('id'),
            'abonos.formaPago',
        ]);

        $formasPago = FormaPago::active()->orderBy('nombre')->get();

        return view('adeudos.show', compact('adeudo', 'formasPago'));
    }

    public function abonar(Request $request, Adeudo $adeudo): RedirectResponse
    {
        if ($adeudo->estatus === Adeudo::ESTATUS_PAGADO) {
            return redirect()->back()
                ->with('error', 'El adeudo ya está liquidado.');
        }

        $validated = $request->validate([
            'monto' => ['required', 'numeric', 'min:0.01'],
            'fecha' => ['nullable', 'date'],
            'forma_pago_id' => ['nullable', 'exists:formas_pago,id'],
        ], $this->mensajesAbono());

        if ((float) $validated['monto'] > $adeudo->pendiente) {
            return redirect()->back()
                ->with('error', 'El abono no puede superar el monto pendiente ('.$adeudo->pendienteFormateado.').');
        }

        AdeudoAbono::create([
            'adeudo_id' => $adeudo->id,
            'forma_pago_id' => $validated['forma_pago_id'] ?? null,
            'monto' => $validated['monto'],
            'fecha' => $validated['fecha'] ?? now(),
        ]);

        $this->recalcularAdeudo($adeudo);

        return redirect()->back()
            ->with('success', 'Abono registrado correctamente.');
    }

    public function abonoUpdate(Request $request, Adeudo $adeudo, AdeudoAbono $abono): JsonResponse
    {
        abort_unless($abono->adeudo_id === $adeudo->id, 404);

        // Solo se validan los campos que el usuario envió: el JS inline
        // deshabilita los que no cambió y los deshabilitados no se envían.
        $datos = $request->only(['monto', 'fecha', 'forma_pago_id']);

        if ($datos !== []) {
            $reglas = collect([
                'monto' => ['required', 'numeric', 'min:0.01'],
                'fecha' => ['nullable', 'date'],
                'forma_pago_id' => ['nullable', 'exists:formas_pago,id'],
            ])->only(array_keys($datos))->all();

            try {
                $datos = $request->validate($reglas, $this->mensajesAbono());
            } catch (ValidationException $e) {
                return response()->json(['errors' => $e->errors()], 422);
            }
        }

        $nuevoMonto = array_key_exists('monto', $datos) ? (float) $datos['monto'] : (float) $abono->monto;
        $otros = (float) $adeudo->abonos()->where('id', '!=', $abono->id)->sum('monto');

        if ($nuevoMonto + $otros > (float) $adeudo->monto) {
            return response()->json([
                'mensaje' => 'El monto total de los abonos no puede superar el monto del adeudo.',
            ], 422);
        }

        $abono->update([
            'monto' => array_key_exists('monto', $datos) ? $datos['monto'] : $abono->monto,
            'fecha' => array_key_exists('fecha', $datos) ? $datos['fecha'] : $abono->fecha,
            'forma_pago_id' => array_key_exists('forma_pago_id', $datos) ? ($datos['forma_pago_id'] ?: null) : $abono->forma_pago_id,
        ]);

        $this->recalcularAdeudo($adeudo);

        return response()->json([
            'success' => true,
            'mensaje' => 'Abono actualizado correctamente.',
            'valores' => [
                'monto' => (string) $abono->fresh()->monto,
                'fecha' => $abono->fresh()->fecha?->format('Y-m-d') ?? '',
                'forma_pago_id' => $abono->fresh()->forma_pago_id,
            ],
        ]);
    }

    private function recalcularAdeudo(Adeudo $adeudo): void
    {
        $pagado = (float) $adeudo->abonos()->sum('monto');

        $adeudo->update([
            'monto_pagado' => min($pagado, (float) $adeudo->monto),
            'estatus' => $pagado >= (float) $adeudo->monto
                ? Adeudo::ESTATUS_PAGADO
                : ($pagado > 0 ? Adeudo::ESTATUS_PARCIAL : Adeudo::ESTATUS_PENDIENTE),
        ]);
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = Adeudo::with(['alumno.gradoEscolar']);

        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        if ($request->filled('estatus')) {
            $query->where('adeudos.estatus', $request->input('estatus'));
        }

        return $query;
    }

    private function reglas(): array
    {
        return [
            'alumno_id' => ['required', 'exists:alumnos,id'],
            'concepto' => ['required', 'string', 'max:255'],
            'anotaciones' => ['nullable', 'string', 'max:1000'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'estatus' => ['required', 'in:pendiente,pagado'],
        ];
    }

    private function mensajes(): array
    {
        return [
            'alumno_id.required' => 'Selecciona un alumno.',
            'concepto.required' => 'Indica el concepto del adeudo.',
            'concepto.max' => 'El concepto no puede superar los 255 caracteres.',
            'anotaciones.max' => 'Las anotaciones no pueden superar los 1000 caracteres.',
            'monto.required' => 'Indica el monto del adeudo.',
            'monto.numeric' => 'El monto debe ser un número.',
            'monto.min' => 'El monto debe ser mayor a cero.',
            'estatus.required' => 'Selecciona un estatus.',
            'estatus.in' => 'El estatus seleccionado no es válido.',
        ];
    }

    private function mensajesAbono(): array
    {
        return [
            'monto.required' => 'Indica el monto del abono.',
            'monto.numeric' => 'El monto debe ser un número.',
            'monto.min' => 'El monto debe ser mayor a cero.',
            'fecha.date' => 'La fecha no es válida.',
            'forma_pago_id.exists' => 'La forma de pago seleccionada no es válida.',
        ];
    }
}
