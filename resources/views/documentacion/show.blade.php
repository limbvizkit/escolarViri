@extends('layouts.app')

@section('title', 'Documentación · ' . $alumno->nombre_completo)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Documentos de {{ $alumno->nombre_completo }}</p>
        <a href="{{ route('documentacion.index') }}" class="btn ip-btn-outline">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>

    <div class="ip-card mb-4">
        <div class="ip-card-header">
            <h5 class="ip-card-title">{{ $alumno->nombre_completo }}</h5>
        </div>
        <div class="ip-card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="ip-detail-label">Grado Escolar</div>
                    <div class="ip-detail-value">{{ $alumno->gradoEscolar->nombre ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Fecha de nacimiento</div>
                    <div class="ip-detail-value">{{ $alumno->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Horario</div>
                    <div class="ip-detail-value">{{ $alumno->horario ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="ip-card mb-4">
        <div class="ip-card-header">
            <h5 class="ip-card-title">Documentos cargados</h5>
        </div>
        <div class="ip-card-body">
            <div class="row g-4">
                @foreach ($tipos as $tipo => $etiqueta)
                    @php
                        $documento = $alumno->documentos->firstWhere('tipo', $tipo);
                    @endphp
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <div class="fw-semibold">{{ $etiqueta }}</div>
                                @if ($documento)
                                    <span class="badge ip-badge-active">OK</span>
                                @else
                                    <span class="badge text-bg-danger">Falta</span>
                                @endif
                            </div>
                            @if ($documento)
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('documentacion.descargar', $documento) }}" class="btn ip-btn-outline btn-sm" title="Descargar">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <form action="{{ route('documentacion.destroy', $documento) }}" method="POST"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar este documento?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ip-action ip-action-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="ip-card">
        <div class="ip-card-header">
            <h5 class="ip-card-title">Cargar o reemplazar documentos</h5>
        </div>
        <div class="ip-card-body">
            <form action="{{ route('documentacion.store', $alumno) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-4">
                    @foreach ($tipos as $tipo => $etiqueta)
                        @php
                            $documento = $alumno->documentos->firstWhere('tipo', $tipo);
                            $field = "documentos.{$tipo}";
                        @endphp
                        <div class="col-md-6">
                            <label for="{{ $tipo }}" class="form-label">{{ $etiqueta }}</label>
                            <input type="file" id="{{ $tipo }}" name="documentos[{{ $tipo }}]"
                                   class="form-control @error($field) is-invalid @enderror"
                                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            @error($field)<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            @if ($documento)
                                <div class="form-text">Selecciona un archivo para reemplazar el documento actual.</div>
                            @else
                                <div class="form-text">PDF, JPG, PNG, DOC o DOCX. Tamaño máximo 5 MB.</div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="ip-form-actions">
                    <a href="{{ route('documentacion.index') }}" class="btn ip-btn-outline">Cancelar</a>
                    <button type="submit" class="btn ip-btn-success">
                        <i class="bi bi-check-lg me-1"></i>Guardar documentos
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection