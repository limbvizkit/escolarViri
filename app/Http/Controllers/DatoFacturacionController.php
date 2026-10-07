<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\DeletesStoredFiles;
use App\Models\Alumno;
use App\Models\DatoFacturacion;
use App\Models\GradoEscolar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatoFacturacionController extends Controller
{
    use DeletesStoredFiles;

    public function index(Request $request): View
    {
        $query = Alumno::has('datosFacturacion')
            ->with([
                'gradoEscolar',
                'datosFacturacion' => fn ($q) => $q->latest(),
            ])
            ->withCount('datosFacturacion');

        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        if ($request->filled('grado_escolar_id')) {
            $query->where('grado_escolar_id', $request->input('grado_escolar_id'));
        }

        $alumnos = $this->paginateOrdered(
            $query,
            $request,
            ['id', 'nombre', 'apellido_paterno', 'apellido_materno'],
            'id',
        );

        $filtros = [
            [
                'name' => 'grado_escolar_id',
                'label' => 'Grado',
                'options' => GradoEscolar::orderBy('nombre')->pluck('nombre', 'id')->all(),
            ],
        ];

        $alumnosDisponibles = Alumno::with('gradoEscolar')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();

        return view('datos-facturacion.index', [
            'alumnos' => $alumnos,
            'filtros' => $filtros,
            'alumnosDisponibles' => $alumnosDisponibles,
        ]);
    }

    public function create(Request $request): View
    {
        $alumnosDisponibles = Alumno::with('gradoEscolar')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get();

        $alumnoSeleccionado = null;
        if ($request->filled('alumno_id')) {
            $alumnoSeleccionado = Alumno::find($request->input('alumno_id'));
        }

        return view('datos-facturacion.create', [
            'alumnosDisponibles' => $alumnosDisponibles,
            'alumnoSeleccionado' => $alumnoSeleccionado,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'alumno_id' => ['required', 'exists:alumnos,id'],
            'documentos' => ['required', 'array', 'min:1'],
            'documentos.*' => ['required', 'file', 'max:25600'],
            'mensaje' => ['nullable', 'string', 'max:2000'],
        ], [
            'alumno_id.required' => 'Debe seleccionar un alumno.',
            'alumno_id.exists' => 'El alumno seleccionado no es válido.',
            'documentos.required' => 'Debe seleccionar al menos un documento.',
            'documentos.array' => 'Los documentos deben enviarse en formato de lista.',
            'documentos.min' => 'Debe seleccionar al menos un documento.',
            'documentos.*.file' => 'Cada documento seleccionado debe ser un archivo válido.',
            'documentos.*.max' => 'Los archivos no deben superar los 25 MB cada uno.',
        ]);

        $alumno = Alumno::findOrFail($validated['alumno_id']);
        $totalCargados = 0;

        foreach ($request->file('documentos') as $archivo) {
            $ruta = $archivo->store('datos-facturacion/'.$alumno->id, 'documents');

            DatoFacturacion::create([
                'alumno_id' => $alumno->id,
                'archivo' => $ruta,
                'nombre_original' => $archivo->getClientOriginalName(),
                'mime_type' => $archivo->getMimeType(),
                'mensaje' => $validated['mensaje'] ?? null,
            ]);

            $totalCargados++;
        }

        return redirect()->route('datos-facturacion.index')
            ->with('success', "Se cargaron exitosamente {$totalCargados} recibo(s) de facturación para {$alumno->nombre_completo}.");
    }

    public function show(Alumno $alumno): View
    {
        $alumno->load([
            'gradoEscolar',
            'datosFacturacion' => fn ($q) => $q->latest(),
        ]);

        return view('datos-facturacion.show', [
            'alumno' => $alumno,
        ]);
    }

    public function descargar(DatoFacturacion $datoFacturacion): StreamedResponse
    {
        $this->authorize('view', $datoFacturacion);

        return $this->downloadStoredFile(
            $datoFacturacion->archivo,
            $datoFacturacion->nombre_original ?: basename($datoFacturacion->archivo),
        );
    }

    public function destroy(DatoFacturacion $datoFacturacion): RedirectResponse
    {
        $this->authorize('delete', $datoFacturacion);

        $alumno = $datoFacturacion->alumno;
        $this->deleteStoredFile($datoFacturacion->archivo);
        $datoFacturacion->delete();

        return back()->with('success', 'Recibo de facturación eliminado correctamente.');
    }
}
