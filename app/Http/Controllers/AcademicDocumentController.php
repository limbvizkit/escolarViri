<?php

namespace App\Http\Controllers;

use App\Models\AcademicDocument;
use App\Models\Alumno;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AcademicDocumentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Alumno::with(['gradoEscolar'])
            ->withCount('academicDocuments');

        if ($request->filled('q')) {
            $query->search($request->input('q'));
        }

        $alumnos = $this->paginateOrdered(
            $query,
            $request,
            ['id', 'nombre', 'apellido_paterno', 'apellido_materno'],
            'id',
        );

        return view('academic-documents.index', [
            'alumnos' => $alumnos,
        ]);
    }

    public function show(Alumno $alumno): View
    {
        $alumno->load(['academicDocuments' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }, 'gradoEscolar']);

        return view('academic-documents.show', [
            'alumno' => $alumno,
        ]);
    }

    public function create(Alumno $alumno): View
    {
        $alumno->load('gradoEscolar');

        return view('academic-documents.create', [
            'alumno' => $alumno,
        ]);
    }

    public function store(Request $request, Alumno $alumno): RedirectResponse
    {
        $validated = $request->validate(
            $this->rules(),
            $this->messages(),
        );

        $this->validateHasContent($request);

        $data = [
            'alumno_id' => $alumno->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'content' => $validated['content'] ?? null,
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['file'] = $file->store('academic-documents/'.$alumno->id, 'public');
            $data['original_name'] = $file->getClientOriginalName();
            $data['mime_type'] = $file->getMimeType();
            $data['content'] = null;
        }

        AcademicDocument::create($data);

        return redirect()->route('academic-documents.show', $alumno)
            ->with('success', 'Documento académico registrado correctamente.');
    }

    public function edit(AcademicDocument $academicDocument): View
    {
        $academicDocument->load('alumno.gradoEscolar');

        return view('academic-documents.edit', [
            'document' => $academicDocument,
            'alumno' => $academicDocument->alumno,
        ]);
    }

    public function update(Request $request, AcademicDocument $academicDocument): RedirectResponse
    {
        $validated = $request->validate(
            $this->rules(),
            $this->messages(),
        );

        $this->validateHasContent($request, $academicDocument);

        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'content' => $validated['content'] ?? null,
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            if ($academicDocument->isFile() && $academicDocument->file !== null) {
                Storage::disk('public')->delete($academicDocument->file);
            }

            $data['file'] = $file->store('academic-documents/'.$academicDocument->alumno_id, 'public');
            $data['original_name'] = $file->getClientOriginalName();
            $data['mime_type'] = $file->getMimeType();
            $data['content'] = null;
        }

        $academicDocument->update($data);

        return redirect()->route('academic-documents.show', $academicDocument->alumno)
            ->with('success', 'Documento académico actualizado correctamente.');
    }

    public function destroy(AcademicDocument $academicDocument): RedirectResponse
    {
        $alumno = $academicDocument->alumno;

        if ($academicDocument->isFile() && $academicDocument->file !== null) {
            Storage::disk('public')->delete($academicDocument->file);
        }

        $academicDocument->delete();

        return redirect()->route('academic-documents.show', $alumno)
            ->with('success', 'Documento académico eliminado correctamente.');
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string', 'max:50000'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png,txt', 'max:10240'],
        ];
    }

    private function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.max' => 'El título no puede superar los 255 caracteres.',
            'description.max' => 'La descripción no puede superar los 1000 caracteres.',
            'content.max' => 'El contenido no puede superar los 50000 caracteres.',
            'file.mimes' => 'El archivo debe ser PDF, Word, JPG, PNG o TXT.',
            'file.max' => 'El archivo no puede superar los 10 MB.',
        ];
    }

    private function validateHasContent(Request $request, ?AcademicDocument $existing = null): void
    {
        $hasFile = $request->hasFile('file');
        $hasContent = filled($request->input('content'));
        $keepsExistingFile = $existing !== null && $existing->isFile() && ! $hasFile;

        if (! $hasFile && ! $hasContent && ! $keepsExistingFile) {
            throw ValidationException::withMessages([
                'content' => 'Debes escribir un contenido o adjuntar un archivo.',
            ]);
        }
    }
}
