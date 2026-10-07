<?php

namespace App\Http\Controllers;

use App\Exports\AlumnoExport;
use App\Http\Controllers\Concerns\DeletesStoredFiles;
use App\Models\Alumno;
use App\Models\AlumnoArchivo;
use App\Models\Estatus;
use App\Models\GradoEscolar;
use App\Models\HorarioExtendido;
use App\Models\Sucursal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AlumnoController extends Controller
{
    use DeletesStoredFiles;

    public function index(Request $request): View
    {
        $query = $this->filteredQuery($request);

        $alumnos = $this->paginateOrdered(
            $query,
            $request,
            $this->allowedSorts(),
            'id',
        );

        $gradosEscolares = $this->gradosDisponibles();

        $filtros = [
            ['name' => 'grado_escolar_id', 'label' => 'Grado Escolar', 'options' => $gradosEscolares->pluck('nombre', 'id')->all()],
            ['name' => 'sucursal_id', 'label' => 'Sucursal', 'options' => Sucursal::active()->orderBy('nombre')->pluck('nombre', 'id')->all()],
            ['name' => 'horario_extendido_id', 'label' => 'Horario extendido', 'options' => HorarioExtendido::active()->orderBy('nombre')->pluck('nombre', 'id')->all()],
            ['name' => 'sexo', 'label' => 'Sexo', 'options' => Alumno::opcionesSexo()],
            ['name' => 'estatus', 'label' => 'Estatus', 'options' => [Estatus::ACTIVO => 'Activo', Estatus::INACTIVO => 'Inactivo']],
        ];

        $sucursales = Sucursal::active()->orderBy('nombre')->get();
        $horariosExtendidos = HorarioExtendido::active()->orderBy('nombre')->get();

        return view('alumnos.index', [
            'alumnos' => $alumnos,
            'filtros' => $filtros,
            'gradosEscolares' => $gradosEscolares,
            'sucursales' => $sucursales,
            'horariosExtendidos' => $horariosExtendidos,
            'rp' => $this->prefijoRuta(),
            'tituloModulo' => $this->tituloModulo(),
        ]);
    }

    public function inlineUpdate(Request $request, Alumno $alumno)
    {
        $datos = $request->only(array_keys($this->reglas()));

        if ($datos !== []) {
            $reglas = collect($this->reglas())->only(array_keys($datos))->all();
            $datos = $request->validate($reglas, $this->mensajes());
        }

        if ($request->has('estatus_id')) {
            $datos['estatus_id'] = (int) $request->input('estatus_id');
        }

        $alumno->update($datos);

        $campos = array_keys($datos);

        return response()->json([
            'success' => true,
            'mensaje' => 'Cambios guardados.',
            'valores' => $alumno->fresh()->only($campos),
        ]);
    }

    public function create(): View
    {
        $gradosEscolares = $this->gradosDisponibles();
        $sucursales = Sucursal::active()->orderBy('nombre')->get();
        $horariosExtendidos = HorarioExtendido::active()->orderBy('nombre')->get();

        return view('alumnos.create', [
            'gradosEscolares' => $gradosEscolares,
            'sucursales' => $sucursales,
            'horariosExtendidos' => $horariosExtendidos,
            'rp' => $this->prefijoRuta(),
            'gradoFijo' => $this->gradoFijo(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->reglas(), $this->mensajes());

        $this->applyNaFlags($request, $validated);
        $this->forzarGrado($validated);

        $datos = $validated + ['estatus_id' => (int) $request->input('estatus_id', Estatus::ACTIVO)];

        if ($request->hasFile('archivo')) {
            $datos['archivo'] = $request->file('archivo')->store('alumnos', 'documents');
        }

        $alumno = Alumno::create($datos);

        $this->guardarArchivosMultiples($request, $alumno);

        return redirect()->route($this->prefijoRuta().'.index')
            ->with('success', 'Alumno creado correctamente.');
    }

    public function show(Alumno $alumno): View
    {
        $alumno->load(['gradoEscolar', 'sucursal', 'horarioExtendido', 'archivos']);

        return view('alumnos.show', [
            'alumno' => $alumno,
            'rp' => $this->prefijoRuta(),
        ]);
    }

    public function edit(Alumno $alumno): View
    {
        $gradosEscolares = $this->gradosDisponibles();
        $sucursales = Sucursal::active()->orderBy('nombre')->get();
        $horariosExtendidos = HorarioExtendido::active()->orderBy('nombre')->get();

        $alumno->load('archivos');

        return view('alumnos.edit', [
            'alumno' => $alumno,
            'gradosEscolares' => $gradosEscolares,
            'sucursales' => $sucursales,
            'horariosExtendidos' => $horariosExtendidos,
            'rp' => $this->prefijoRuta(),
            'gradoFijo' => $this->gradoFijo(),
        ]);
    }

    public function update(Request $request, Alumno $alumno): RedirectResponse
    {
        $validated = $request->validate($this->reglas(), $this->mensajes());

        $this->applyNaFlags($request, $validated);
        $this->forzarGrado($validated);

        $datos = $validated + ['estatus_id' => (int) $request->input('estatus_id', Estatus::ACTIVO)];

        if ($request->hasFile('archivo')) {
            $this->deleteStoredFile($alumno->archivo);

            $datos['archivo'] = $request->file('archivo')->store('alumnos', 'documents');
        }

        $alumno->update($datos);

        $this->guardarArchivosMultiples($request, $alumno);

        return redirect()->route($this->prefijoRuta().'.index')
            ->with('success', 'Alumno actualizado correctamente.');
    }

    public function destroy(Alumno $alumno): RedirectResponse
    {
        $alumno->update(['estatus_id' => Estatus::ELIMINADO]);

        return redirect()->route($this->prefijoRuta().'.index')
            ->with('success', 'Alumno eliminado correctamente.');
    }

    public function exportPdf(Request $request)
    {
        $alumnos = $this->filteredQuery($request)
            ->orderBy($this->sortField($request, $this->allowedSorts(), 'id'), $this->sortDirection($request))
            ->get();

        $pdf = Pdf::loadView('alumnos.pdf', compact('alumnos'))->setPaper('a4', 'landscape');

        return $pdf->download($this->prefijoRuta().'-'.now()->format('Y-m-d').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $query = $this->filteredQuery($request)
            ->orderBy($this->sortField($request, $this->allowedSorts(), 'id'), $this->sortDirection($request));

        return Excel::download(new AlumnoExport($query), $this->prefijoRuta().'-'.now()->format('Y-m-d').'.xlsx');
    }

    public function uploadArchivo(Request $request, Alumno $alumno): RedirectResponse
    {
        $request->validate($this->reglasArchivos(), $this->mensajesArchivos());

        $this->guardarArchivosMultiples($request, $alumno);

        return redirect()->route($this->prefijoRuta().'.edit', $alumno)
            ->with('success', 'Archivo(s) cargado(s) correctamente.');
    }

    public function destroyArchivo(Alumno $alumno, AlumnoArchivo $archivo): RedirectResponse
    {
        abort_unless($archivo->alumno_id === $alumno->id, 404);

        $this->deleteStoredFile($archivo->archivo);

        $archivo->delete();

        return redirect()->route($this->prefijoRuta().'.edit', $alumno)
            ->with('success', 'Archivo eliminado correctamente.');
    }

    public function downloadArchivo(Alumno $alumno, AlumnoArchivo $archivo): StreamedResponse
    {
        abort_unless($archivo->alumno_id === $alumno->id, 404);

        return $this->downloadStoredFile(
            $archivo->archivo,
            $archivo->nombre_original ?? basename($archivo->archivo)
        );
    }

    public function downloadLegacyArchivo(Alumno $alumno): StreamedResponse
    {
        abort_if($alumno->archivo === null || $alumno->archivo === '', 404);

        return $this->downloadStoredFile(
            $alumno->archivo,
            basename($alumno->archivo)
        );
    }

    private function guardarArchivosMultiples(Request $request, Alumno $alumno): void
    {
        if (! $request->hasFile('archivos')) {
            return;
        }

        foreach ($request->file('archivos') as $archivo) {
            $ruta = $archivo->store('alumnos', 'documents');

            $alumno->archivos()->create([
                'archivo' => $ruta,
                'nombre_original' => $archivo->getClientOriginalName(),
            ]);
        }
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = Alumno::with(['gradoEscolar', 'sucursal', 'horarioExtendido']);

        $this->aplicarAlcanceGrado($query);

        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        if ($request->filled('grado_escolar_id')) {
            $query->where('alumnos.grado_escolar_id', $request->input('grado_escolar_id'));
        }

        if ($request->filled('sucursal_id')) {
            $query->where('alumnos.sucursal_id', $request->input('sucursal_id'));
        }

        if ($request->filled('horario_extendido_id')) {
            $query->where('alumnos.horario_extendido_id', $request->input('horario_extendido_id'));
        }

        if ($request->filled('sexo')) {
            $query->where('alumnos.sexo', $request->input('sexo'));
        }

        if ($request->filled('estatus')) {
            $query->where('alumnos.estatus_id', $request->input('estatus'));
        }

        return $query;
    }

    /**
     * Indica si el controlador opera en modo Estimulación Temprana.
     */
    protected function esEstimulacionTemprana(): bool
    {
        return false;
    }

    /**
     * Prefijo de las rutas del módulo (alumnos o estimulacion-temprana).
     */
    protected function prefijoRuta(): string
    {
        return 'alumnos';
    }

    protected function tituloModulo(): string
    {
        return 'Alumnos';
    }

    protected function gradoEstimulacionTemprana(): ?GradoEscolar
    {
        return GradoEscolar::query()
            ->where('slug', GradoEscolar::SLUG_ESTIMULACION_TEMPRANA)
            ->first();
    }

    /**
     * Grado fijo del módulo (Estimulación Temprana) o null si no aplica.
     */
    protected function gradoFijo(): ?GradoEscolar
    {
        return $this->esEstimulacionTemprana() ? $this->gradoEstimulacionTemprana() : null;
    }

    /**
     * Grados escolares que puede usar el módulo: en modo Estimulación Temprana
     * solo ese grado; en modo normal, todos menos ese.
     */
    protected function gradosDisponibles()
    {
        $et = $this->gradoEstimulacionTemprana();

        if ($this->esEstimulacionTemprana()) {
            return $et ? collect([$et]) : collect();
        }

        $query = GradoEscolar::active()->orderBy('nombre');

        if ($et) {
            $query->whereKeyNot($et->id);
        }

        return $query->get();
    }

    private function aplicarAlcanceGrado(Builder $query): void
    {
        $et = $this->gradoEstimulacionTemprana();

        if ($this->esEstimulacionTemprana()) {
            if ($et) {
                $query->where('alumnos.grado_escolar_id', $et->id);
            } else {
                $query->whereRaw('1 = 0');
            }

            return;
        }

        if ($et) {
            $query->where(function (Builder $q) use ($et) {
                $q->where('alumnos.grado_escolar_id', '!=', $et->id)
                    ->orWhereNull('alumnos.grado_escolar_id');
            });
        }
    }

    private function forzarGrado(array &$datos): void
    {
        if (! $this->esEstimulacionTemprana()) {
            return;
        }

        $et = $this->gradoEstimulacionTemprana();

        if ($et) {
            $datos['grado_escolar_id'] = $et->id;
        }
    }

    private function reglasGrado(): array
    {
        $reglas = ['required', 'exists:grados_escolares,id'];
        $et = $this->gradoEstimulacionTemprana();

        if ($et === null) {
            return $reglas;
        }

        $reglas[] = $this->esEstimulacionTemprana()
            ? Rule::in([$et->id])
            : Rule::notIn([$et->id]);

        return $reglas;
    }

    private function allowedSorts(): array
    {
        return ['id', 'nombre', 'apellido_paterno', 'apellido_materno', 'sexo', 'fecha_nacimiento', 'horario',
            'horario_extendido_id', 'inscripcion', 'reinscripcion', 'entrevista_inicial', 'nat_geo', 'cuota_materiales',
            'fecha_ingreso', 'cuota_mensual', 'estatus_id', 'sucursal_id'];
    }

    private function applyNaFlags(Request $request, array &$validated): void
    {
        foreach ($this->camposFinancieros() as $campo) {
            if ($request->boolean($campo.'_na')) {
                $validated[$campo] = null;
            }
        }
    }

    private function camposFinancieros(): array
    {
        // Solo la cuota mensual conserva el monto con opción "NA".
        return ['cuota_mensual'];
    }

    private function reglas(): array
    {
        return array_merge($this->reglasBase(), [
            'archivo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
        ]);
    }

    private function reglasBase(): array
    {
        return [
            'grado_escolar_id' => $this->reglasGrado(),
            'sucursal_id' => ['nullable', 'exists:sucursales,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'sexo' => ['required', 'string', Rule::in([Alumno::SEXO_NINO, Alumno::SEXO_NINA])],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'horario' => ['nullable', 'string', 'max:50'],
            'horario_extendido_id' => ['nullable', 'exists:horarios_extendidos,id'],
            'inscripcion' => ['nullable', Rule::in(Alumno::CONCEPTOS_ESTADO)],
            'reinscripcion' => ['nullable', Rule::in(Alumno::CONCEPTOS_ESTADO)],
            'entrevista_inicial' => ['nullable', Rule::in(Alumno::CONCEPTOS_ESTADO)],
            'nat_geo' => ['nullable', Rule::in(Alumno::CONCEPTOS_ESTADO)],
            'cuota_materiales' => ['nullable', Rule::in(Alumno::CONCEPTOS_ESTADO)],
            'fecha_ingreso' => ['nullable', 'date'],
            'cuota_mensual' => ['nullable', 'numeric', 'min:0'],
            'estatus_id' => ['nullable', 'exists:estatus,id'],
        ];
    }

    private function reglasArchivos(): array
    {
        return [
            'archivos' => ['required', 'array'],
            'archivos.*' => ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
        ];
    }

    private function mensajes(): array
    {
        return array_merge($this->mensajesBase(), [
            'archivo.mimes' => 'El archivo adjunto debe ser PDF, JPG, JPEG, PNG, DOC o DOCX.',
            'archivo.max' => 'El archivo adjunto no puede superar los 5 MB.',
        ]);
    }

    private function mensajesBase(): array
    {
        return [
            'grado_escolar_id.required' => 'Selecciona un grado escolar.',
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido_paterno.required' => 'El apellido paterno es obligatorio.',
            'sexo.required' => 'Selecciona el sexo del alumno.',
            'sexo.in' => 'El sexo debe ser Niño o Niña.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'inscripcion.in' => 'La inscripción debe ser SI, NO APLICA o PENDIENTE.',
            'reinscripcion.in' => 'La re/inscripción debe ser SI, NO APLICA o PENDIENTE.',
            'entrevista_inicial.in' => 'La entrevista inicial debe ser SI, NO APLICA o PENDIENTE.',
            'nat_geo.in' => 'Nat Geo debe ser SI, NO APLICA o PENDIENTE.',
            'cuota_materiales.in' => 'La cuota de materiales debe ser SI, NO APLICA o PENDIENTE.',
        ];
    }

    private function mensajesArchivos(): array
    {
        return [
            'archivos.required' => 'Selecciona al menos un archivo.',
            'archivos.*.mimes' => 'Cada archivo debe ser PDF, JPG, JPEG, PNG, DOC o DOCX.',
            'archivos.*.max' => 'Cada archivo no puede superar los 5 MB.',
        ];
    }
}
