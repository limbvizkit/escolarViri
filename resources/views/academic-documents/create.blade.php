@extends('layouts.app')

@section('title', 'Nuevo documento académico · ' . $alumno->nombre_completo)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Registrar documento académico para {{ $alumno->nombre_completo }}</p>
        <a href="{{ route('academic-documents.show', $alumno) }}" class="btn ip-btn-outline">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>

    <div class="ip-card">
        <div class="ip-card-header">
            <h5 class="ip-card-title">Nuevo documento académico</h5>
        </div>
        <div class="ip-card-body">
            <form action="{{ route('academic-documents.store', $alumno) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-12">
                        <label for="title" class="form-label">
                            Título <span class="ip-required">*</span>
                        </label>
                        <input type="text" id="title" name="title"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title') }}" required>
                        @error('title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea id="description" name="description" rows="2"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                        @error('description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label for="content" class="form-label">Contenido de texto</label>
                        <textarea id="content" name="content" rows="6"
                                  class="form-control @error('content') is-invalid @enderror">{{ old('content') }}</textarea>
                        @error('content')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        <div class="form-text">Puedes escribir contenido de texto o adjuntar un archivo.</div>
                    </div>

                    <div class="col-12">
                        <label for="file" class="form-label">Archivo adjunto</label>
                        <input type="file" id="file" name="file"
                               class="form-control @error('file') is-invalid @enderror"
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt">
                        @error('file')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        <div class="form-text">PDF, Word, JPG, PNG o TXT. Tamaño máximo 10 MB.</div>
                    </div>
                </div>

                <div class="ip-form-actions">
                    <a href="{{ route('academic-documents.show', $alumno) }}" class="btn ip-btn-outline">Cancelar</a>
                    <button type="submit" class="btn ip-btn-success">
                        <i class="bi bi-check-lg me-1"></i>Guardar documento
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
