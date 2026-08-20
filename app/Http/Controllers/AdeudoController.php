<?php

namespace App\Http\Controllers;

use App\Models\Adeudo;
use App\Models\AdeudoAbono;
use App\Models\Alumno;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $adeudo->load(['alumno.gradoEscolar', 'abonos' => fn ($q) => $q->orderByDesc('fecha')->orderByDesc('id')]);

        return view('adeudos.show', compact('adeudo'));
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
        ], $this->mensajesAbono());

        if ((float) $validated['monto'] > $adeudo->pendiente) {
            return redirect()->back()
                ->with('error', 'El abono no puede superar el monto pendiente ('.$adeudo->pendienteFormateado.').');
        }

        AdeudoAbono::create([
            'adeudo_id' => $adeudo->id,
            'monto' => $validated['monto'],
            'fecha' => $validated['fecha'] ?? now(),
        ]);

        $nuevoPagado = (float) $adeudo->monto_pagado + (float) $validated['monto'];
        $adeudo->update([
            'monto_pagado' => min($nuevoPagado, (float) $adeudo->monto),
            'estatus' => $nuevoPagado >= (float) $adeudo->monto ? Adeudo::ESTATUS_PAGADO : Adeudo::ESTATUS_PARCIAL,
        ]);

        return redirect()->back()
            ->with('success', 'Abono registrado correctamente.');
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
        ];
    }
}
