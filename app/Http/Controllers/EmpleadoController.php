<?php

namespace App\Http\Controllers;

use App\Exports\EmpleadoExport;
use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\Sucursal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class EmpleadoController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->filteredQuery($request);

        $empleados = $this->paginateOrdered(
            $query,
            $request,
            $this->allowedSorts(),
            'apellido_paterno',
        );

        $filtros = [
            ['name' => 'sucursal_id', 'label' => 'Sucursal', 'options' => Sucursal::orderBy('nombre')->pluck('nombre', 'id')->all()],
            ['name' => 'estatus', 'label' => 'Estatus', 'options' => [Estatus::ACTIVO => 'Activo', Estatus::INACTIVO => 'Inactivo']],
        ];

        return view('empleados.index', compact('empleados', 'filtros'));
    }

    public function exportPdf(Request $request)
    {
        $empleados = $this->filteredQuery($request)
            ->orderBy($this->sortField($request, $this->allowedSorts(), 'apellido_paterno'), $this->sortDirection($request))
            ->get();

        $pdf = Pdf::loadView('empleados.pdf', compact('empleados'))->setPaper('a4', 'landscape');

        return $pdf->download('empleados-'.now()->format('Y-m-d').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $query = $this->filteredQuery($request)
            ->orderBy($this->sortField($request, $this->allowedSorts(), 'apellido_paterno'), $this->sortDirection($request));

        return Excel::download(new EmpleadoExport($query), 'empleados-'.now()->format('Y-m-d').'.xlsx');
    }

    public function create(): View
    {
        $sucursales = Sucursal::with('escuela')->active()->orderBy('nombre')->get();

        return view('empleados.create', compact('sucursales'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        Empleado::create($validated + ['estatus_id' => (int) $request->input('estatus_id', Estatus::ACTIVO)]);

        return redirect()->route('empleados.index')
            ->with('success', 'Empleado creado correctamente.');
    }

    public function show(Empleado $empleado): View
    {
        $empleado->load(['sucursal', 'usuario']);

        return view('empleados.show', compact('empleado'));
    }

    public function edit(Empleado $empleado): View
    {
        $sucursales = Sucursal::with('escuela')->active()->orderBy('nombre')->get();

        return view('empleados.edit', compact('empleado', 'sucursales'));
    }

    public function update(Request $request, Empleado $empleado): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $empleado->update($validated + ['estatus_id' => (int) $request->input('estatus_id', Estatus::ACTIVO)]);

        return redirect()->route('empleados.index')
            ->with('success', 'Empleado actualizado correctamente.');
    }

    public function destroy(Empleado $empleado): RedirectResponse
    {
        $empleado->update(['estatus_id' => Estatus::ELIMINADO]);

        return redirect()->route('empleados.index')
            ->with('success', 'Empleado eliminado correctamente.');
    }

    private function filteredQuery(Request $request): Builder
    {
        $query = Empleado::with('sucursal');

        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        if ($request->filled('estatus')) {
            $query->where('empleados.estatus_id', $request->input('estatus'));
        }

        if ($request->filled('sucursal_id')) {
            $query->where('empleados.sucursal_id', $request->input('sucursal_id'));
        }

        return $query;
    }

    private function allowedSorts(): array
    {
        return ['id', 'nombre', 'apellido_paterno', 'apellido_materno', 'email', 'puesto', 'horario', 'fecha_nacimiento', 'tipo_sangre', 'curp', 'estatus_id'];
    }

    /**
     * Reglas de validación compartidas para crear y actualizar empleados.
     */
    private function rules(): array
    {
        return [
            'sucursal_id' => ['required', 'exists:sucursales,id'],
            'nombre' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'puesto' => ['nullable', 'string', 'max:255'],
            'horario' => ['nullable', 'string', 'max:255'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'numeros_emergencia' => ['nullable', 'string', 'max:1000'],
            'tipo_sangre' => ['nullable', 'string', 'max:10'],
            'enfermedad' => ['nullable', 'string', 'max:1000'],
            'alergias' => ['nullable', 'string', 'max:1000'],
            'medicamento' => ['nullable', 'string', 'max:1000'],
            'direccion' => ['nullable', 'string', 'max:1000'],
            'telefono_personal' => ['nullable', 'string', 'max:30'],
            'curp' => ['nullable', 'string', 'max:18'],
            'estatus_id' => ['nullable', 'exists:estatus,id'],
        ];
    }
}
