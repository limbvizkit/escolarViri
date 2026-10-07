@extends('layouts.app')

@section('title', 'Cargar Recibos de Facturación')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold">Cargar Recibos de Facturación</h4>
            <p class="ip-muted mb-0">Selecciona un alumno, agrega los documentos y el mensaje correspondiente</p>
        </div>
        <a href="{{ route('datos-facturacion.index') }}" class="btn ip-btn-outline">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>Por favor revisa los errores:
            <ul class="mb-0 mt-1 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <div class="ip-card">
        <div class="ip-card-header">
            <h5 class="ip-card-title">Formulario de Carga</h5>
        </div>
        <div class="ip-card-body">
            <form action="{{ route('datos-facturacion.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3">
                    <div class="col-12">
                        <label for="alumno_id" class="form-label fw-semibold">
                            Seleccionar Alumno <span class="ip-required">*</span>
                        </label>
                        <select name="alumno_id" id="alumno_id" class="form-select @error('alumno_id') is-invalid @enderror" required>
                            <option value="">-- Seleccione un alumno --</option>
                            @foreach ($alumnosDisponibles as $al)
                                <option value="{{ $al->id }}" @selected(old('alumno_id', $alumnoSeleccionado?->id) == $al->id)>
                                    {{ $al->apellido_paterno }} {{ $al->apellido_materno }} {{ $al->nombre }} ({{ $al->gradoEscolar->nombre ?? 'Sin grado' }})
                                </option>
                            @endforeach
                        </select>
                        @error('alumno_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="documentos" class="form-label fw-semibold">
                            Documentos / Recibos de Facturación <span class="ip-required">*</span>
                        </label>
                        <input type="file" name="documentos[]" id="documentos"
                               class="form-control @error('documentos') is-invalid @enderror @error('documentos.*') is-invalid @enderror"
                               multiple required
                               accept=".pdf,.xml,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx">
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>Puedes seleccionar uno o varios documentos a la vez (PDF, XML, comprobantes, imágenes o archivos de Office. Máximo 25 MB por archivo).
                        </div>
                        @error('documentos')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        @error('documentos.*')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="mensaje" class="form-label fw-semibold">
                            Mensaje u observaciones
                        </label>
                        <textarea name="mensaje" id="mensaje" rows="4"
                                  class="form-control @error('mensaje') is-invalid @enderror"
                                  placeholder="Escribe un mensaje, notas o datos sobre estos recibos de facturación...">{{ old('mensaje') }}</textarea>
                        @error('mensaje')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="ip-form-actions mt-4">
                    <a href="{{ route('datos-facturacion.index') }}" class="btn ip-btn-outline">Cancelar</a>
                    <button type="submit" class="btn ip-btn-success">
                        <i class="bi bi-check-lg me-1"></i>Guardar documentos
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
