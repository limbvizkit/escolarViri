<?php

namespace App\Http\Controllers;

use App\Exports\AlumnoExport;
use App\Models\Alumno;
use App\Models\Estatus;
use App\Models\GradoEscolar;
use App\Models\Sucursal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

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
            ['name' => 'estatus', 'label' => 'Estatus', 'options' => [Estatus::ACTIVO => 'Activo', Estatus::INACTIVO => 'Inactivo']],
        ];

        $gradosEscolares = GradoEscolar::active()->orderBy('nombre')->get();
        $sucursales = Sucursal::active()->orderBy('nombre')->get();

        return view('alumnos.index', compact('alumnos', 'filtros', 'gradosEscolares', 'sucursales'));
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

        return view('alumnos.create', compact('gradosEscolares', 'sucursales'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->reglas(), $this->mensajes());

        $this->applyNaFlags($request, $validated);

        $datos = $validated + ['estatus_id' => (int) $request->input('estatus_id', Estatus::ACTIVO)];

        if ($request->hasFile('archivo')) {
            $datos['archivo'] = $request->file('archivo')->store('alumnos', 'public');
        }

        Alumno::create($datos);

        return redirect()->route('alumnos.index')
            ->with('success', 'Alumno creado correctamente.');
    }

    public function show(Alumno $alumno): View
    {
        $alumno->load(['gradoEscolar', 'sucursal']);

        return view('alumnos.show', compact('alumno'));
    }

    public function edit(Alumno $alumno): View
    {
        $gradosEscolares = GradoEscolar::active()->orderBy('nombre')->get();
        $sucursales = Sucursal::active()->orderBy('nombre')->get();

        return view('alumnos.edit', compact('alumno', 'gradosEscolares', 'sucursales'));
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

    private function filteredQuery(Request $request): Builder
    {
        $query = Alumno::with(['gradoEscolar', 'sucursal']);

        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        if ($request->filled('grado_escolar_id')) {
            $query->where('alumnos.grado_escolar_id', $request->input('grado_escolar_id'));
        }

        if ($request->filled('sucursal_id')) {
            $query->where('alumnos.sucursal_id', $request->input('sucursal_id'));
        }

        if ($request->filled('estatus')) {
            $query->where('alumnos.estatus_id', $request->input('estatus'));
        }

        return $query;
    }

    private function allowedSorts(): array
    {
        return ['id', 'nombre', 'apellido_paterno', 'apellido_materno', 'fecha_nacimiento', 'horario',
            'inscripcion', 'reinscripcion', 'entrevista_inicial', 'nat_geo', 'cuota_materiales',
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
        return [
            'grado_escolar_id' => ['required', 'exists:grados_escolares,id'],
            'sucursal_id' => ['nullable', 'exists:sucursales,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'horario' => ['nullable', 'string', 'max:50'],
            'inscripcion' => ['nullable', 'numeric', 'min:0'],
            'reinscripcion' => ['nullable', 'numeric', 'min:0'],
            'entrevista_inicial' => ['nullable', 'numeric', 'min:0'],
            'nat_geo' => ['nullable', 'numeric', 'min:0'],
            'cuota_materiales' => ['nullable', 'numeric', 'min:0'],
            'fecha_ingreso' => ['nullable', 'date'],
            'cuota_mensual' => ['nullable', 'numeric', 'min:0'],
            'estatus_id' => ['nullable', 'exists:estatus,id'],
            'archivo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
        ];
    }

    private function mensajes(): array
    {
        return [
            'grado_escolar_id.required' => 'Selecciona un grado escolar.',
            'nombre.required' => 'El nombre es obligatorio.',
            'apellido_paterno.required' => 'El apellido paterno es obligatorio.',
            'fecha_nacimiento.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
        ];
    }
}
