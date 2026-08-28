<?php

namespace App\Http\Controllers;

use App\Exports\AlumnoExport;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AlumnoController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->filteredQuery($request);

        $alumnos = $this->paginateOrdered(
            $query,
            $request,
            $this->allowedSorts(),
            'id',
        );

        $filtros = [
            ['name' => 'grado_escolar_id', 'label' => 'Grado Escolar', 'options' => GradoEscolar::orderBy('nombre')->pluck('nombre', 'id')->all()],
            ['name' => 'sucursal_id', 'label' => 'Sucursal', 'options' => Sucursal::active()->orderBy('nombre')->pluck('nombre', 'id')->all()],
            ['name' => 'horario_extendido_id', 'label' => 'Horario extendido', 'options' => HorarioExtendido::active()->orderBy('nombre')->pluck('nombre', 'id')->all()],
            ['name' => 'estatus', 'label' => 'Estatus', 'options' => [Estatus::ACTIVO => 'Activo', Estatus::INACTIVO => 'Inactivo']],
        ];

        $gradosEscolares = GradoEscolar::active()->orderBy('nombre')->get();
        $sucursales = Sucursal::active()->orderBy('nombre')->get();
        $horariosExtendidos = HorarioExtendido::active()->orderBy('nombre')->get();

        return view('alumnos.index', compact('alumnos', 'filtros', 'gradosEscolares', 'sucursales', 'horariosExtendidos'));
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
        $gradosEscolares = GradoEscolar::active()->orderBy('nombre')->get();
        $sucursales = Sucursal::active()->orderBy('nombre')->get();
        $horariosExtendidos = HorarioExtendido::active()->orderBy('nombre')->get();

        return view('alumnos.create', compact('gradosEscolares', 'sucursales', 'horariosExtendidos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->reglas(), $this->mensajes());

        $this->applyNaFlags($request, $validated);

        $datos = $validated + ['estatus_id' => (int) $request->input('estatus_id', Estatus::ACTIVO)];

        if ($request->hasFile('archivo')) {
            $datos['archivo'] = $request->file('archivo')->store('alumnos', 'public');
        }

        $alumno = Alumno::create($datos);

        $this->guardarArchivosMultiples($request, $alumno);

        return redirect()->route('alumnos.index')
            ->with('success', 'Alumno creado correctamente.');
    }

    public function show(Alumno $alumno): View
    {
        $alumno->load(['gradoEscolar', 'sucursal', 'horarioExtendido', 'archivos']);

        return view('alumnos.show', compact('alumno'));
    }

    public function edit(Alumno $alumno): View
    {
        $gradosEscolares = GradoEscolar::active()->orderBy('nombre')->get();
        $sucursales = Sucursal::active()->orderBy('nombre')->get();
        $horariosExtendidos = HorarioExtendido::active()->orderBy('nombre')->get();

        $alumno->load('archivos');

        return view('alumnos.edit', compact('alumno', 'gradosEscolares', 'sucursales', 'horariosExtendidos'));
    }

    public function update(Request $request, Alumno $alumno): RedirectResponse
    {
        $validated = $request->validate($this->reglas(), $this->mensajes());

        $this->applyNaFlags($request, $validated);

        $datos = $validated + ['estatus_id' => (int) $request->input('estatus_id', Estatus::ACTIVO)];

        if ($request->hasFile('archivo')) {
            if ($alumno->archivo) {
                Storage::disk('public')->delete($alumno->archivo);
            }

            $datos['archivo'] = $request->file('archivo')->store('alumnos', 'public');
        }

        $alumno->update($datos);

        $this->guardarArchivosMultiples($request, $alumno);

        return redirect()->route('alumnos.index')
            ->with('success', 'Alumno actualizado correctamente.');
    }

    public function destroy(Alumno $alumno): RedirectResponse
    {
        $alumno->update(['estatus_id' => Estatus::ELIMINADO]);

        return redirect()->route('alumnos.index')
            ->with('success', 'Alumno eliminado correctamente.');
    }

    public function exportPdf(Request $request)
    {
        $alumnos = $this->filteredQuery($request)
            ->orderBy($this->sortField($request, $this->allowedSorts(), 'id'), $this->sortDirection($request))
            ->get();

        $pdf = Pdf::loadView('alumnos.pdf', compact('alumnos'))->setPaper('a4', 'landscape');

        return $pdf->download('alumnos-'.now()->format('Y-m-d').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $query = $this->filteredQuery($request)
            ->orderBy($this->sortField($request, $this->allowedSorts(), 'id'), $this->sortDirection($request));

        return Excel::download(new AlumnoExport($query), 'alumnos-'.now()->format('Y-m-d').'.xlsx');
    }

    public function uploadArchivo(Request $request, Alumno $alumno): RedirectResponse
    {
        $request->validate($this->reglasArchivos(), $this->mensajesArchivos());

        $this->guardarArchivosMultiples($request, $alumno);

        return redirect()->route('alumnos.edit', $alumno)
            ->with('success', 'Archivo(s) cargado(s) correctamente.');
    }

    public function destroyArchivo(Alumno $alumno, AlumnoArchivo $archivo): RedirectResponse
    {
        abort_unless($archivo->alumno_id === $alumno->id, 404);

        Storage::disk('public')->delete($archivo->archivo);

        $archivo->delete();

        return redirect()->route('alumnos.edit', $alumno)
            ->with('success', 'Archivo eliminado correctamente.');
    }

    public function downloadArchivo(Alumno $alumno, AlumnoArchivo $archivo): StreamedResponse
    {
        abort_unless($archivo->alumno_id === $alumno->id, 404);

        return Storage::disk('public')->download(
            $archivo->archivo,
            $archivo->nombre_original ?? basename($archivo->archivo)
        );
    }

    private function guardarArchivosMultiples(Request $request, Alumno $alumno): void
    {
        if (! $request->hasFile('archivos')) {
            return;
        }

        foreach ($request->file('archivos') as $archivo) {
            $ruta = $archivo->store('alumnos', 'public');

            $alumno->archivos()->create([
                'archivo' => $ruta,
                'nombre_original' => $archivo->getClientOriginalName(),
            ]);
        }
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = Alumno::with(['gradoEscolar', 'sucursal', 'horarioExtendido']);

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

        if ($request->filled('estatus')) {
            $query->where('alumnos.estatus_id', $request->input('estatus'));
        }

        return $query;
    }

    private function allowedSorts(): array
    {
        return ['id', 'nombre', 'apellido_paterno', 'apellido_materno', 'fecha_nacimiento', 'horario',
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
        return ['inscripcion', 'reinscripcion', 'entrevista_inicial', 'nat_geo', 'cuota_materiales', 'cuota_mensual'];
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
            'grado_escolar_id' => ['required', 'exists:grados_escolares,id'],
            'sucursal_id' => ['nullable', 'exists:sucursales,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'horario' => ['nullable', 'string', 'max:50'],
            'horario_extendido_id' => ['nullable', 'exists:horarios_extendidos,id'],
            'inscripcion' => ['nullable', 'numeric', 'min:0'],
            'reinscripcion' => ['nullable', 'numeric', 'min:0'],
            'entrevista_inicial' => ['nullable', 'numeric', 'min:0'],
            'nat_geo' => ['nullable', 'numeric', 'min:0'],
            'cuota_materiales' => ['nullable', 'numeric', 'min:0'],
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
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
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
