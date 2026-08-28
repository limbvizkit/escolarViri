@php
    $esImagen = function (string $ruta): bool {
        return in_array(strtolower(pathinfo($ruta, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
    };

    $archivosExistentes = $alumno->archivos;
    $archivoLegacy = $alumno->archivo;
    $tieneArchivos = $archivosExistentes->isNotEmpty() || $archivoLegacy;
@endphp

@extends('layouts.app')

@section('title', $alumno->nombre_completo)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Detalle del alumno</p>
        <a href="{{ route('alumnos.edit', $alumno) }}" class="btn ip-btn">
            <i class="bi bi-pencil-square me-1"></i>Editar
        </a>
    </div>

    <div class="ip-card mb-4">
        <div class="ip-card-header">
            <h5 class="ip-card-title">{{ $alumno->nombre_completo }}</h5>
            <div class="d-flex align-items-center gap-2">
                @if ($alumno->gradoEscolar)
                    <span class="badge" style="background:#eaf1ff;color:var(--ip-primary);font-weight:600;">
                        {{ $alumno->gradoEscolar->nombre }}
                    </span>
                @endif
                <span class="badge ip-badge-{{ $alumno->estatus_badge }}">
                    {{ $alumno->estatus_es_activo ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
        </div>

        <div class="ip-card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="ip-detail-label">Nombre(s)</div>
                    <div class="ip-detail-value">{{ $alumno->nombre }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Apellido paterno</div>
                    <div class="ip-detail-value">{{ $alumno->apellido_paterno }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Apellido materno</div>
                    <div class="ip-detail-value">{{ $alumno->apellido_materno ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Grado Escolar</div>
                    <div class="ip-detail-value">{{ $alumno->gradoEscolar->nombre ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Sucursal</div>
                    <div class="ip-detail-value">{{ $alumno->sucursal->nombre ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Horario</div>
                    <div class="ip-detail-value">{{ $alumno->horario ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Horario extendido</div>
                    <div class="ip-detail-value">{{ $alumno->horarioExtendido->nombre ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Fecha de nacimiento</div>
                    <div class="ip-detail-value">{{ $alumno->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Fecha de ingreso</div>
                    <div class="ip-detail-value">{{ $alumno->fecha_ingreso?->format('d/m/Y') ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Inscripción</div>
                    <div class="ip-detail-value">{{ $alumno->inscripcion ? '$' . number_format($alumno->inscripcion, 2) : 'NA' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Re/Inscripción</div>
                    <div class="ip-detail-value">{{ $alumno->reinscripcion ? '$' . number_format($alumno->reinscripcion, 2) : 'NA' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Entrevista inicial</div>
                    <div class="ip-detail-value">{{ $alumno->entrevista_inicial ? '$' . number_format($alumno->entrevista_inicial, 2) : 'NA' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Nat Geo</div>
                    <div class="ip-detail-value">{{ $alumno->nat_geo ? '$' . number_format($alumno->nat_geo, 2) : 'NA' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Cuota de materiales</div>
                    <div class="ip-detail-value">{{ $alumno->cuota_materiales ? '$' . number_format($alumno->cuota_materiales, 2) : 'NA' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Cuota mensual</div>
                    <div class="ip-detail-value">{{ $alumno->cuota_mensual ? '$' . number_format($alumno->cuota_mensual, 2) : 'NA' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="ip-card mb-4">
        <div class="ip-card-header">
            <h5 class="ip-card-title">Archivos adjuntos</h5>
        </div>
        <div class="ip-card-body">
            @if ($tieneArchivos)
                <div class="row g-4">
                    @foreach ($archivosExistentes as $archivoItem)
                        @php
                            $urlArchivo = Storage::url($archivoItem->archivo);
                            $imagenArchivo = $esImagen($archivoItem->archivo);
                            $nombreArchivo = $archivoItem->nombre_original ?? basename($archivoItem->archivo);
                        @endphp
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-3">
                                @if ($imagenArchivo)
                                    <div class="ip-doc-thumb" data-bs-toggle="modal" data-bs-target="#archivoModal"
                                         data-bs-src="{{ $urlArchivo }}" data-bs-title="{{ $nombreArchivo }}"
                                         role="button" tabindex="0" title="Ver {{ $nombreArchivo }}">
                                        <img src="{{ $urlArchivo }}" alt="{{ $nombreArchivo }}">
                                    </div>
                                @else
                                    <a href="{{ $urlArchivo }}" target="_blank" rel="noopener noreferrer"
                                       class="ip-doc-thumb" title="Ver {{ $nombreArchivo }}">
                                        <i class="bi bi-file-earmark-text ip-doc-icon"></i>
                                    </a>
                                @endif
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold text-truncate" title="{{ $nombreArchivo }}">{{ $nombreArchivo }}</div>
                                    <span class="badge ip-badge-active">Guardado</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('alumnos.archivos.download', [$alumno, $archivoItem]) }}"
                                       class="btn ip-btn-outline btn-sm" title="Descargar">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if ($archivoLegacy)
                        @php
                            $urlLegacy = Storage::url($archivoLegacy);
                            $imagenLegacy = $esImagen($archivoLegacy);
                            $nombreLegacy = basename($archivoLegacy);
                        @endphp
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-3">
                                @if ($imagenLegacy)
                                    <div class="ip-doc-thumb" data-bs-toggle="modal" data-bs-target="#archivoModal"
                                         data-bs-src="{{ $urlLegacy }}" data-bs-title="{{ $nombreLegacy }}"
                                         role="button" tabindex="0" title="Ver {{ $nombreLegacy }}">
                                        <img src="{{ $urlLegacy }}" alt="{{ $nombreLegacy }}">
                                    </div>
                                @else
                                    <a href="{{ $urlLegacy }}" target="_blank" rel="noopener noreferrer"
                                       class="ip-doc-thumb" title="Ver {{ $nombreLegacy }}">
                                        <i class="bi bi-file-earmark-text ip-doc-icon"></i>
                                    </a>
                                @endif
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold text-truncate" title="{{ $nombreLegacy }}">{{ $nombreLegacy }}</div>
                                    <span class="badge text-bg-warning">Histórico</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ $urlLegacy }}" target="_blank" rel="noopener noreferrer"
                                       class="btn ip-btn-outline btn-sm" title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <p class="ip-muted mb-0">No hay archivos adjuntos.</p>
            @endif
        </div>
    </div>

    <div class="modal fade" id="archivoModal" tabindex="-1" aria-labelledby="archivoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="archivoModalLabel">Vista previa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="" id="archivoModalImg" class="img-fluid" alt="Vista previa">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';

            const modal = document.getElementById('archivoModal');
            if (!modal) return;

            const img = document.getElementById('archivoModalImg');
            const title = document.getElementById('archivoModalLabel');

            modal.addEventListener('show.bs.modal', function (event) {
                const trigger = event.relatedTarget;
                img.src = trigger.getAttribute('data-bs-src');
                title.textContent = trigger.getAttribute('data-bs-title');
            });
        })();
    </script>
@endpush
