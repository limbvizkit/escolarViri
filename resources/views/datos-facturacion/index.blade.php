@extends('layouts.app')

@section('title', 'Datos Facturación')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold">Datos Facturación</h4>
            <p class="ip-muted mb-0">Gestión de recibos y documentos de facturación por alumno</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn ip-btn" data-bs-toggle="modal" data-bs-target="#modalCargarFacturacion">
                <i class="bi bi-cloud-arrow-up-fill me-1"></i>Cargar recibos
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>Por favor revisa los errores del formulario:
            <ul class="mb-0 mt-1 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    @include('partials.table-filters', [
        'filters' => $filtros,
        'placeholder' => 'Buscar alumno por nombre o apellidos...',
    ])

    <div class="ip-card">
        <div class="ip-card-header">
            <span class="ip-table-summary">Mostrando {{ $alumnos->currentPage() }} de {{ $alumnos->lastPage() }}</span>
            <h5 class="ip-card-title">Alumnos con documentos de facturación cargados</h5>
        </div>

        <div class="table-responsive">
            <table class="table ip-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th style="width: 250px;">Alumno</th>
                        <th style="width: 150px;">Grado</th>
                        <th>Documentos cargados</th>
                        <th class="text-end" style="width: 150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($alumnos as $alumno)
                        <tr>
                            <td class="text-muted">{{ $alumno->id }}</td>
                            <td>
                                <a href="{{ route('datos-facturacion.show', $alumno) }}" class="fw-semibold text-decoration-none text-dark d-block">
                                    {{ $alumno->nombre_completo }}
                                </a>
                                <span class="badge bg-secondary-subtle text-secondary small">
                                    {{ $alumno->datos_facturacion_count }} {{ Str::plural('recibo', $alumno->datos_facturacion_count) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge ip-badge-forma-pago">{{ $alumno->gradoEscolar->nombre ?? '—' }}</span>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-2 py-1">
                                    @foreach ($alumno->datosFacturacion as $doc)
                                        <div class="p-2 border rounded bg-light-subtle d-flex flex-wrap justify-content-between align-items-center gap-2">
                                            <div class="d-flex align-items-center gap-2 flex-grow-1">
                                                <i class="bi {{ $doc->fileIcon() }} fs-5 text-primary"></i>
                                                <div>
                                                    <a href="{{ $doc->fileUrl() }}" class="fw-semibold text-decoration-none text-body" title="Descargar {{ $doc->nombre_original }}">
                                                        {{ $doc->nombre_original }}
                                                    </a>
                                                    <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                                        <span class="badge bg-light text-dark border small" title="Fecha y hora en que se guardó el documento">
                                                            <i class="bi bi-clock-fill text-primary me-1"></i>{{ $doc->created_at->format('d/m/Y H:i') }}
                                                        </span>
                                                        @if ($doc->mensaje)
                                                            <span class="small text-muted">
                                                                <i class="bi bi-chat-left-text me-1"></i>{{ $doc->mensaje }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-1">
                                                <a href="{{ $doc->fileUrl() }}" class="btn btn-sm btn-outline-primary py-0 px-2" title="Descargar documento">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                <form action="{{ route('datos-facturacion.destroy', $doc) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('¿Seguro que deseas eliminar este recibo de facturación?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Eliminar recibo">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <button type="button" class="btn ip-btn btn-sm btn-cargar-alumno"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalCargarFacturacion"
                                            data-alumno-id="{{ $alumno->id }}"
                                            data-alumno-nombre="{{ $alumno->nombre_completo }}"
                                            title="Agregar más recibos a este alumno">
                                        <i class="bi bi-plus-lg me-1"></i>Cargar
                                    </button>
                                    <a href="{{ route('datos-facturacion.show', $alumno) }}" class="btn ip-btn-outline btn-sm" title="Ver detalle completo">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center ip-muted py-5">
                                <i class="bi bi-receipt fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No hay alumnos con documentos de facturación cargados.
                                <div class="mt-2">
                                    <button type="button" class="btn ip-btn btn-sm" data-bs-toggle="modal" data-bs-target="#modalCargarFacturacion">
                                        <i class="bi bi-cloud-arrow-up-fill me-1"></i>Cargar el primer recibo
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ip-card-body d-flex justify-content-center">
            {{ $alumnos->links() }}
        </div>
    </div>

    <!-- Modal para Cargar Recibos de Facturación -->
    <div class="modal fade" id="modalCargarFacturacion" tabindex="-1" aria-labelledby="modalCargarFacturacionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalCargarFacturacionLabel">
                        <i class="bi bi-receipt me-2 text-primary"></i>Cargar Recibos de Facturación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form action="{{ route('datos-facturacion.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="alumno_id" class="form-label fw-semibold">
                                    Seleccionar Alumno <span class="ip-required">*</span>
                                </label>
                                <select name="alumno_id" id="alumno_id" class="form-select @error('alumno_id') is-invalid @enderror" required>
                                    <option value="">-- Seleccione un alumno --</option>
                                    @foreach ($alumnosDisponibles as $al)
                                        <option value="{{ $al->id }}" @selected(old('alumno_id') == $al->id)>
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
                                <textarea name="mensaje" id="mensaje" rows="3"
                                          class="form-control @error('mensaje') is-invalid @enderror"
                                          placeholder="Escribe un mensaje, notas o datos sobre estos recibos de facturación...">{{ old('mensaje') }}</textarea>
                                @error('mensaje')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAlumno = document.getElementById('alumno_id');
            const btnsCargar = document.querySelectorAll('.btn-cargar-alumno');

            btnsCargar.forEach(btn => {
                btn.addEventListener('click', function () {
                    const alumnoId = this.getAttribute('data-alumno-id');
                    if (selectAlumno && alumnoId) {
                        selectAlumno.value = alumnoId;
                    }
                });
            });

            @if ($errors->any())
                const modalElement = document.getElementById('modalCargarFacturacion');
                if (modalElement && typeof bootstrap !== 'undefined') {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            @endif
        });
    </script>
@endsection
