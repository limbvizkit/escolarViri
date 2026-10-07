@extends('layouts.app')

@section('title', 'Datos Facturación · ' . $alumno->nombre_completo)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold">{{ $alumno->nombre_completo }}</h4>
            <p class="ip-muted mb-0">Recibos y documentos de facturación registrados</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('datos-facturacion.index') }}" class="btn ip-btn-outline">
                <i class="bi bi-arrow-left me-1"></i>Volver
            </a>
            <button type="button" class="btn ip-btn" data-bs-toggle="modal" data-bs-target="#modalCargarFacturacionAlumno">
                <i class="bi bi-plus-lg me-1"></i>Cargar más recibos
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <div class="ip-card mb-4">
        <div class="ip-card-header">
            <h5 class="ip-card-title">Información del Alumno</h5>
        </div>
        <div class="ip-card-body">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="ip-detail-label">Grado Escolar</div>
                    <div class="ip-detail-value">{{ $alumno->gradoEscolar->nombre ?? '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="ip-detail-label">Horario</div>
                    <div class="ip-detail-value">{{ $alumno->horario ?? '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="ip-detail-label">Fecha de nacimiento</div>
                    <div class="ip-detail-value">{{ $alumno->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="ip-detail-label">Total de recibos</div>
                    <div class="ip-detail-value fw-bold text-primary">{{ $alumno->datosFacturacion->count() }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="ip-card">
        <div class="ip-card-header">
            <h5 class="ip-card-title">Documentos y Recibos Guardados</h5>
        </div>

        <div class="table-responsive">
            <table class="table ip-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th style="width: 50px;">Tipo</th>
                        <th>Documento</th>
                        <th>Mensaje / Observaciones</th>
                        <th style="width: 200px;">Fecha y hora de guardado</th>
                        <th class="text-end" style="width: 140px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($alumno->datosFacturacion as $doc)
                        <tr>
                            <td>
                                <i class="bi {{ $doc->fileIcon() }} fs-4 text-primary"></i>
                            </td>
                            <td>
                                <a href="{{ $doc->fileUrl() }}" class="fw-semibold text-decoration-none text-body" title="Descargar {{ $doc->nombre_original }}">
                                    {{ $doc->nombre_original }}
                                </a>
                                <div class="small text-muted">{{ strtoupper($doc->fileExtension()) }}</div>
                            </td>
                            <td>
                                @if ($doc->mensaje)
                                    <span><i class="bi bi-chat-left-text me-1 text-muted"></i>{{ $doc->mensaje }}</span>
                                @else
                                    <span class="text-muted fst-italic">Sin mensaje</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="bi bi-clock-fill text-primary me-1"></i>{{ $doc->created_at->format('d/m/Y H:i') }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ $doc->fileUrl() }}" class="btn btn-sm btn-outline-primary" title="Descargar documento">
                                        <i class="bi bi-download me-1"></i>Descargar
                                    </a>
                                    <form action="{{ route('datos-facturacion.destroy', $doc) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar este recibo de facturación?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar recibo">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center ip-muted py-4">
                                No hay documentos cargados para este alumno.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para Cargar Recibos a este Alumno -->
    <div class="modal fade" id="modalCargarFacturacionAlumno" tabindex="-1" aria-labelledby="modalCargarFacturacionAlumnoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalCargarFacturacionAlumnoLabel">
                        <i class="bi bi-receipt me-2 text-primary"></i>Cargar Recibos para {{ $alumno->nombre_completo }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form action="{{ route('datos-facturacion.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="alumno_id" value="{{ $alumno->id }}">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="documentos" class="form-label fw-semibold">
                                    Documentos / Recibos de Facturación <span class="ip-required">*</span>
                                </label>
                                <input type="file" name="documentos[]" id="documentos" class="form-control"
                                       multiple required
                                       accept=".pdf,.xml,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx">
                                <div class="form-text">
                                    Puedes seleccionar uno o varios documentos a la vez (PDF, XML, comprobantes, imágenes o archivos de Office. Máximo 25 MB por archivo).
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="mensaje" class="form-label fw-semibold">
                                    Mensaje u observaciones
                                </label>
                                <textarea name="mensaje" id="mensaje" rows="3" class="form-control"
                                          placeholder="Escribe un mensaje, notas o datos sobre estos recibos de facturación..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn ip-btn-outline" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn ip-btn">
                            <i class="bi bi-cloud-arrow-up-fill me-1"></i>Guardar documentos
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
