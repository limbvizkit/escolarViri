<?php

namespace App\Http\Controllers;

use App\Exports\PagoExport;
use App\Models\Alumno;
use App\Models\FormaPago;
use App\Models\Pago;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class PagoController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->filteredQuery($request);

        $pagos = $this->paginateOrdered(
            $query,
            $request,
            $this->allowedSorts(),
            'alumno_id',
        );

        $alumnos = Alumno::with('gradoEscolar')->orderBy('apellido_paterno')->get();
        $formasPago = FormaPago::active()->orderBy('nombre')->get();

        $meses = Pago::query()->distinct()->orderByDesc('mes')->pluck('mes')->all();
        $mesOptions = array_combine(
            $meses,
            array_map(fn ($mes) => Pago::mesLabel($mes), $meses),
        );

        $filtros = [
            ['name' => 'mes', 'label' => 'Mes', 'options' => $mesOptions],
            ['name' => 'forma_pago_id', 'label' => 'Forma de pago', 'options' => FormaPago::orderBy('nombre')->pluck('nombre', 'id')->all()],
        ];

        return view('pagos.index', compact('pagos', 'filtros', 'formasPago'));
    }

    public function inlineUpdate(Request $request, Pago $pago)
    {
        $datos = $request->only(array_keys($this->reglas()));

        if ($datos !== []) {
            $reglas = collect($this->reglas())->only(array_keys($datos))->all();
            $datos = $request->validate($reglas, $this->mensajes());
        }

        try {
            $pago->update($datos);
        } catch (QueryException) {
            return response()->json([
                'mensaje' => 'No se pudo guardar: ya existe un pago de ese mes para el alumno seleccionado.',
            ], 422);
        }

        $campos = array_keys($datos);

        return response()->json([
            'success' => true,
            'mensaje' => 'Cambios guardados.',
            'valores' => $pago->fresh()->only($campos),
        ]);
    }

    public function create(): View
    {
        $alumnos = Alumno::with('gradoEscolar')->orderBy('apellido_paterno')->get();
        $formasPago = FormaPago::active()->orderBy('nombre')->get();

        return view('pagos.create', compact('alumnos', 'formasPago'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->reglas(), $this->mensajes());

        Pago::create($validated);

        return redirect()->route('pagos.index')
            ->with('success', 'Pago registrado correctamente.');
    }

    public function show(Pago $pago): View
    {
        $pago->load(['alumno.gradoEscolar', 'formaPago']);

        return view('pagos.show', compact('pago'));
    }

    public function edit(Pago $pago): View
    {
        $alumnos = Alumno::with('gradoEscolar')->orderBy('apellido_paterno')->get();
        $formasPago = FormaPago::active()->orderBy('nombre')->get();

        return view('pagos.edit', compact('pago', 'alumnos', 'formasPago'));
    }

    public function update(Request $request, Pago $pago): RedirectResponse
    {
        $validated = $request->validate($this->reglas(), $this->mensajes());

        $pago->update($validated);

        return redirect()->route('pagos.index')
            ->with('success', 'Pago actualizado correctamente.');
    }

    public function destroy(Pago $pago): RedirectResponse
    {
        $pago->delete();

        return redirect()->route('pagos.index')
            ->with('success', 'Pago eliminado correctamente.');
    }

    public function exportPdf(Request $request)
    {
        $pagos = $this->filteredQuery($request)
            ->orderBy($this->sortField($request, $this->allowedSorts(), 'alumno_id'), $this->sortDirection($request))
            ->get();

        $pdf = Pdf::loadView('pagos.pdf', compact('pagos'))->setPaper('a4', 'landscape');

        return $pdf->download('pagos-'.now()->format('Y-m-d').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $query = $this->filteredQuery($request)
            ->orderBy($this->sortField($request, $this->allowedSorts(), 'alumno_id'), $this->sortDirection($request));

        return Excel::download(new PagoExport($query), 'pagos-'.now()->format('Y-m-d').'.xlsx');
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = Pago::with(['alumno.gradoEscolar', 'formaPago']);

        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        if ($request->filled('mes')) {
            $query->where('pagos.mes', $request->input('mes'));
        }

        if ($request->filled('forma_pago_id')) {
            $query->where('pagos.forma_pago_id', $request->input('forma_pago_id'));
        }

        return $query;
    }

    private function allowedSorts(): array
    {
        return ['id', 'alumno_id', 'mes', 'fecha', 'entrada_8am', 'pronto_pago', 'pago_normal', 'talleres', 'lunch', 'forma_pago_id'];
    }

    private function reglas(): array
    {
        return [
            'alumno_id' => ['required', 'exists:alumnos,id'],
            'mes' => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'fecha' => ['nullable', 'date'],
            'entrada_8am' => ['nullable', 'numeric', 'min:0'],
            'pronto_pago' => ['nullable', 'numeric', 'min:0'],
            'pago_normal' => ['nullable', 'numeric', 'min:0'],
            'talleres' => ['nullable', 'numeric', 'min:0'],
            'lunch' => ['nullable', 'numeric', 'min:0'],
            'forma_pago_id' => ['nullable', 'exists:formas_pago,id'],
        ];
    }

    private function mensajes(): array
    {
        return [
            'alumno_id.required' => 'Selecciona un alumno.',
            'mes.required' => 'Indica el mes del pago.',
            'mes.regex' => 'El formato del mes es inválido.',
        ];
    }
}
