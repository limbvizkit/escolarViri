@extends('layouts.app')

@section('title', 'Documentación Académica · ' . $alumno->nombre_completo)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Documentos académicos de {{ $alumno->nombre_completo }}</p>
        <a href="{{ route('academic-documents.index') }}" class="btn ip-btn-outline">
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
            <h5 class="ip-card-title">Documentos académicos</h5>
            <a href="{{ route('academic-documents.create', $alumno) }}" class="btn ip-btn btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Nuevo documento académico
            </a>
        </div>

        <div class="table-responsive">
            <table class="table ip-table mb-0">
                <thead>
                    <tr>
                        <th class="text-center">Vista previa</th>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($alumno->academicDocuments as $document)
                        <tr>
                            <td class="text-center">
                                @if ($document->isImage())
                                    <a href="{{ $document->fileUrl() }}" target="_blank" rel="noopener noreferrer"
                                       class="ip-doc-thumb" title="Ver {{ $document->title }}">
                                        <img src="{{ $document->fileUrl() }}" alt="{{ $document->title }}">
                                    </a>
                                @elseif ($document->isFile())
                                    <a href="{{ $document->fileUrl() }}" target="_blank" rel="noopener noreferrer"
                                       class="ip-doc-thumb" title="Ver {{ $document->title }}">
                                        <i class="bi {{ $document->fileIcon() }} ip-doc-icon"></i>
                                    </a>
                                @else
                                    <div class="ip-doc-thumb" title="Entrada de texto">
                                        <i class="bi bi-file-text ip-doc-icon"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $document->title }}</td>
                            <td>
                                <span class="badge ip-badge-forma-pago">{{ $document->typeLabel() }}</span>
                            </td>
                            <td>
                                @if ($document->isText())
                                    <span class="ip-cell-wrap">{{ Str::limit($document->content, 120) }}</span>
                                @elseif ($document->description)
                                    <span class="ip-cell-wrap">{{ Str::limit($document->description, 120) }}</span>
                                @else
                                    <span class="ip-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    @if ($document->isFile())
                                        <a href="{{ $document->fileUrl() }}" target="_blank" rel="noopener noreferrer"
                                           class="ip-action" title="Ver archivo">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('academic-documents.edit', $document) }}" class="ip-action" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('academic-documents.destroy', $document) }}" method="POST"
                                          class="d-inline ip-delete-form"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar este documento académico?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ip-action ip-action-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center ip-muted py-4">
                                No hay documentos académicos registrados para este alumno.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
