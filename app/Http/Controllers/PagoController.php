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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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

    public function precargar(): View
    {
        $mesActual = now()->format('Y-m');
        $mesSiguiente = now()->startOfMonth()->addMonthNoOverflow()->format('Y-m');

        $pagosActuales = $this->pagosDelMes($mesActual);
        $pagosSiguientes = $this->pagosDelMes($mesSiguiente);
        $formasPago = FormaPago::active()->orderBy('nombre')->get();

        $alumnosConPagoSiguiente = $pagosSiguientes->pluck('alumno_id')->all();
        $pagosActuales = $pagosActuales
            ->reject(fn ($pago) => in_array($pago->alumno_id, $alumnosConPagoSiguiente, true))
            ->values();

        $etiquetaMesActual = Pago::mesLabel($mesActual);
        $etiquetaMesSiguiente = Pago::mesLabel($mesSiguiente);

        return view('pagos.precargar', compact(
            'mesSiguiente',
            'etiquetaMesActual',
            'etiquetaMesSiguiente',
            'pagosActuales',
            'pagosSiguientes',
            'formasPago',
        ));
    }

    public function precargarStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'seleccionados' => ['required', 'array', 'min:1'],
            'seleccionados.*' => ['exists:pagos,id'],
        ], [
            'seleccionados.required' => 'Selecciona al menos un pago para precargar.',
            'seleccionados.min' => 'Selecciona al menos un pago para precargar.',
        ]);

        $mesSiguiente = now()->startOfMonth()->addMonthNoOverflow()->format('Y-m');
        $seleccionados = array_map('intval', $validated['seleccionados']);
        $filas = (array) $request->input('pagos', []);
        $origenes = Pago::with('alumno')->whereIn('id', $seleccionados)->get()->keyBy('id');

        foreach ($seleccionados as $id) {
            $origen = $origenes->get($id);

            if ($origen === null) {
                continue;
            }

            $fila = array_intersect_key((array) ($filas[$id] ?? []), $this->reglasPrecarga());
            $validador = Validator::make($fila, array_intersect_key($this->reglasPrecarga(), $fila), $this->mensajes());

            if ($validador->fails()) {
                return redirect()
                    ->route('pagos.precargar')
                    ->withInput()
                    ->with('error', 'No se pudo precargar el pago de '.$origen->alumno->nombre_completo.': '.$validador->errors()->first());
            }
        }

        try {
            [$creados, $omitidos] = DB::transaction(function () use ($filas, $mesSiguiente, $origenes, $seleccionados) {
                $creados = 0;
                $omitidos = [];

                foreach ($seleccionados as $id) {
                    $origen = $origenes->get($id);

                    if ($origen === null) {
                        continue;
                    }

                    $datos = $this->datosParaNuevoPago($origen, $mesSiguiente, (array) ($filas[$id] ?? []));

                    if (Pago::where('alumno_id', $origen->alumno_id)->where('mes', $datos['mes'])->exists()) {
                        $omitidos[] = $origen->alumno->nombre_completo;

                        continue;
                    }

                    Pago::create($datos);
                    $creados++;
                }

                return [$creados, $omitidos];
            });
        } catch (QueryException) {
            return redirect()
                ->route('pagos.precargar')
                ->with('error', 'Ocurrió un error al guardar los pagos. Intenta nuevamente.');
        }

        if ($creados === 0) {
            return redirect()
                ->route('pagos.precargar')
                ->with('error', 'No se crearon pagos nuevos. Se omitieron '.count($omitidos).': '.implode(', ', $omitidos).'.');
        }

        $mensaje = "Se crearon {$creados} pagos para ".Pago::mesLabel($mesSiguiente).'.';

        if ($omitidos !== []) {
            $mensaje .= ' Se omitieron '.count($omitidos).': '.implode(', ', $omitidos).'.';
        }

        return redirect()->route('pagos.precargar')->with('success', $mensaje);
    }

    private function pagosDelMes(string $mes)
    {
        return Pago::with(['alumno.gradoEscolar', 'formaPago'])
            ->where('mes', $mes)
            ->get()
            ->sortBy(fn ($pago) => [$pago->alumno->apellido_paterno, $pago->alumno->nombre])
            ->values();
    }

    private function reglasPrecarga(): array
    {
        return collect($this->reglas())->except('alumno_id')->all();
    }

    private function datosParaNuevoPago(Pago $origen, string $mesSiguiente, array $fila): array
    {
        $datos = [
            'alumno_id' => $origen->alumno_id,
            'mes' => $mesSiguiente,
            'fecha' => $origen->fecha?->copy()->addMonthNoOverflow(),
            'entrada_8am' => $origen->entrada_8am,
            'pronto_pago' => $origen->pronto_pago,
            'pago_normal' => $origen->pago_normal,
            'talleres' => $origen->talleres,
            'lunch' => $origen->lunch,
            'forma_pago_id' => $origen->forma_pago_id,
        ];

        foreach (array_keys($this->reglasPrecarga()) as $campo) {
            if (! array_key_exists($campo, $fila)) {
                continue;
            }

            $valor = $fila[$campo];
            $datos[$campo] = ($valor === null || $valor === '') ? null : $valor;
        }

        return $datos;
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
