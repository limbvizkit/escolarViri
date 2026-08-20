<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Documento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentacionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Alumno::with(['documentos', 'gradoEscolar']);

        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        $alumnos = $this->paginateOrdered(
            $query,
            $request,
            ['id', 'nombre', 'apellido_paterno', 'apellido_materno'],
            'id',
        );

        return view('documentacion.index', [
            'alumnos' => $alumnos,
            'tipos' => Documento::TIPOS,
            'shortLabels' => $this->shortLabels(),
        ]);
    }

    public function show(Alumno $alumno): View
    {
        $alumno->load(['documentos', 'gradoEscolar']);

        return view('documentacion.show', [
            'alumno' => $alumno,
            'tipos' => Documento::TIPOS,
        ]);
    }

    public function store(Request $request, Alumno $alumno): RedirectResponse
    {
        $validated = $request->validate($this->reglas(), $this->mensajes());

        foreach (($validated['documentos'] ?? []) as $tipo => $archivo) {
            $existente = Documento::where('alumno_id', $alumno->id)
                ->where('tipo', $tipo)
                ->first();

            $ruta = $archivo->store('documentos/'.$alumno->id, 'public');

            if ($existente && $existente->archivo !== $ruta) {
                Storage::disk('public')->delete($existente->archivo);
            }

            Documento::updateOrCreate(
                ['alumno_id' => $alumno->id, 'tipo' => $tipo],
                ['archivo' => $ruta],
            );
        }

        return redirect()->route('documentacion.show', $alumno)
            ->with('success', 'Documentos cargados correctamente.');
    }

    public function descargar(Documento $documento)
    {
        return Storage::disk('public')->download($documento->archivo);
    }

    public function destroy(Documento $documento): RedirectResponse
    {
        $alumno = $documento->alumno;

        Storage::disk('public')->delete($documento->archivo);

        $documento->delete();

        return redirect()->route('documentacion.show', $alumno)
            ->with('success', 'Documento eliminado correctamente.');
    }

    private function reglas(): array
    {
        $reglas = [];

        foreach (Documento::TIPOS as $tipo => $etiqueta) {
            $reglas["documentos.$tipo"] = ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'];
        }

        return $reglas;
    }

    private function mensajes(): array
    {
        $mensajes = [];

        foreach (Documento::TIPOS as $tipo => $etiqueta) {
            $mensajes["documentos.$tipo.mimes"] = "El archivo de {$etiqueta} debe ser PDF, JPG, JPEG, PNG, DOC o DOCX.";
            $mensajes["documentos.$tipo.max"] = "El archivo de {$etiqueta} no puede superar los 5 MB.";
        }

        return $mensajes;
    }

    private function shortLabels(): array
    {
        return [
            'acta_nacimiento' => 'Acta',
            'curp' => 'CURP',
            'cartilla_vacunacion' => 'Cart. Vac.',
            'carta_pediatra' => 'Pediatra',
            'reglamento' => 'Reglamento',
            'carta_compromiso' => 'Compromiso',
            'entrevista_inicial' => 'Entrevista',
            'datos_generales' => 'Datos Gral.',
        ];
    }
}
