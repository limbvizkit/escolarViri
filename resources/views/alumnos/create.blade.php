@php
    $alumno = $alumno ?? null;
    $editing = isset($alumno);
    $title = $editing ? 'Editar alumno' : 'Nuevo alumno';
    $action = $editing ? route('alumnos.update', $alumno) : route('alumnos.store');
    $method = $editing ? 'PUT' : 'POST';
    $presetGradoEscolar = request()->query('grado_escolar_id', $alumno->grado_escolar_id ?? '');

    $archivosExistentes = $editing ? $alumno->archivos : collect();
    $archivoLegacy = $editing && $alumno->archivo ? $alumno->archivo : null;

    $esImagen = function (string $ruta): bool {
        return in_array(strtolower(pathinfo($ruta, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
    };
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Datos del alumno</h5>
                </div>

                <div class="ip-card-body">
                    @if ($editing && ($archivosExistentes->isNotEmpty() || $archivoLegacy))
                        <h6 class="fw-semibold text-uppercase small text-secondary mb-3">
                            <i class="bi bi-folder me-1"></i>Archivos adjuntos
                        </h6>
                        <div class="row g-3 mb-4">
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
                                            <form action="{{ route('alumnos.archivos.destroy', [$alumno, $archivoItem]) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('¿Seguro que deseas eliminar este archivo?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="ip-action ip-action-danger" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
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
                    @endif

                    <form action="{{ $action }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method($method)

                        {{-- Grado escolar y horario --}}
                        <h6 class="fw-semibold text-uppercase small text-secondary mb-3">
                            <i class="bi bi-grid-1x2 me-1"></i>Clasificación
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="grado_escolar_id" class="form-label">Grado Escolar <span class="ip-required">*</span></label>
                                <select id="grado_escolar_id" name="grado_escolar_id"
                                        class="form-select @error('grado_escolar_id') is-invalid @enderror" required>
                                    <option value="">— Seleccionar grado escolar —</option>
                                    @foreach ($gradosEscolares as $gradoEscolar)
                                        <option value="{{ $gradoEscolar->id }}"
                                            {{ old('grado_escolar_id', $presetGradoEscolar) == $gradoEscolar->id ? 'selected' : '' }}>
                                            {{ $gradoEscolar->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('grado_escolar_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="sucursal_id" class="form-label">Sucursal</label>
                                <select id="sucursal_id" name="sucursal_id"
                                        class="form-select @error('sucursal_id') is-invalid @enderror">
                                    <option value="">— Seleccionar sucursal —</option>
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id }}"
                                            {{ old('sucursal_id', $alumno->sucursal_id ?? '') == $sucursal->id ? 'selected' : '' }}>
                                            {{ $sucursal->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('sucursal_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="horario" class="form-label">Horario</label>
                                <input type="text" id="horario" name="horario"
                                       class="form-control @error('horario') is-invalid @enderror"
                                       value="{{ old('horario', $alumno->horario ?? '') }}"
                                       placeholder="Ej. LUNES, MARTES, 9:00-1:00">
                                @error('horario')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                <div class="form-text">Un día de la semana o un rango de horas.</div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="horario_extendido_id" class="form-label">Horario extendido</label>
                                <select id="horario_extendido_id" name="horario_extendido_id"
                                        class="form-select @error('horario_extendido_id') is-invalid @enderror">
                                    <option value="">— Seleccionar horario extendido —</option>
                                    @foreach ($horariosExtendidos as $horarioExtendido)
                                        <option value="{{ $horarioExtendido->id }}"
                                            {{ old('horario_extendido_id', $alumno->horario_extendido_id ?? '') == $horarioExtendido->id ? 'selected' : '' }}>
                                            {{ $horarioExtendido->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('horario_extendido_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Nombre completo separado --}}
                        <h6 class="fw-semibold text-uppercase small text-secondary mb-3">
                            <i class="bi bi-person-vcard me-1"></i>Nombre completo
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="nombre" class="form-label">Nombre(s) <span class="ip-required">*</span></label>
                                <input type="text" id="nombre" name="nombre"
                                       class="form-control @error('nombre') is-invalid @enderror"
                                       value="{{ old('nombre', $alumno->nombre ?? '') }}" required>
                                @error('nombre')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="apellido_paterno" class="form-label">Apellido paterno <span class="ip-required">*</span></label>
                                <input type="text" id="apellido_paterno" name="apellido_paterno"
                                       class="form-control @error('apellido_paterno') is-invalid @enderror"
                                       value="{{ old('apellido_paterno', $alumno->apellido_paterno ?? '') }}" required>
                                @error('apellido_paterno')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="apellido_materno" class="form-label">Apellido materno</label>
                                <input type="text" id="apellido_materno" name="apellido_materno"
                                       class="form-control @error('apellido_materno') is-invalid @enderror"
                                       value="{{ old('apellido_materno', $alumno->apellido_materno ?? '') }}">
                                @error('apellido_materno')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Fechas --}}
                        <h6 class="fw-semibold text-uppercase small text-secondary mb-3">
                            <i class="bi bi-calendar-event me-1"></i>Fechas
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento</label>
                                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                                       class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                                       value="{{ old('fecha_nacimiento', $alumno?->fecha_nacimiento?->format('Y-m-d') ?? '') }}">
                                @error('fecha_nacimiento')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="fecha_ingreso" class="form-label">Fecha de ingreso</label>
                                <input type="date" id="fecha_ingreso" name="fecha_ingreso"
                                       class="form-control @error('fecha_ingreso') is-invalid @enderror"
                                       value="{{ old('fecha_ingreso', $alumno?->fecha_ingreso?->format('Y-m-d') ?? '') }}">
                                @error('fecha_ingreso')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Datos financieros --}}
                        <h6 class="fw-semibold text-uppercase small text-secondary mb-3">
                            <i class="bi bi-cash-coin me-1"></i>Información financiera
                        </h6>
                        <div class="row g-3 mb-4">
                            @php
                                $montos = [
                                    'inscripcion' => 'Inscripción',
                                    'reinscripcion' => 'Re/Inscripción',
                                    'entrevista_inicial' => 'Entrevista inicial',
                                    'nat_geo' => 'Nat Geo',
                                    'cuota_materiales' => 'Cuota materiales',
                                    'cuota_mensual' => 'Cuota mensual',
                                ];
                            @endphp
                            @foreach ($montos as $campo => $etiqueta)
                                @php
                                    $na = (bool) old($campo . '_na', $alumno ? $alumno->{$campo} === null : false);
                                @endphp
                                <div class="col-md-4">
                                    <label for="{{ $campo }}" class="form-label">{{ $etiqueta }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" min="0" id="{{ $campo }}" name="{{ $campo }}"
                                               class="form-control @error($campo) is-invalid @enderror"
                                               value="{{ old($campo, $alumno->$campo ?? '') }}"
                                               @if ($na) disabled @endif>
                                        <span class="input-group-text">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input js-na-checkbox" type="checkbox"
                                                       id="{{ $campo }}_na" name="{{ $campo }}_na" value="1"
                                                       data-na-target="{{ $campo }}" title="No aplica"
                                                       {{ $na ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="{{ $campo }}_na">NA</label>
                                            </div>
                                        </span>
                                    </div>
                                    @error($campo)<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                            @endforeach
                        </div>

                        {{-- Archivo adjunto --}}
                        <h6 class="fw-semibold text-uppercase small text-secondary mb-3">
                            <i class="bi bi-paperclip me-1"></i>Archivo adjunto
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <div id="archivos-preview" class="d-flex flex-wrap gap-3 mb-3"></div>

                                <label for="archivos" class="form-label">Archivos</label>
                                <input type="file" id="archivos" name="archivos[]"
                                       class="form-control @error('archivos.*') is-invalid @enderror"
                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" multiple>
                                @error('archivos.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                @error('archivos')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                <div class="form-text">PDF, JPG, PNG, DOC o DOCX. Tamaño máximo 5 MB por archivo.</div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-4">
                            <label for="estatus_id" class="form-label d-block">Estatus</label>
                            <select id="estatus_id" name="estatus_id"
                                    class="form-select @error('estatus_id') is-invalid @enderror">
                                <option value="1" @selected((int) old('estatus_id', $alumno->estatus_id ?? 1) === 1)>Activo</option>
                                <option value="2" @selected((int) old('estatus_id', $alumno->estatus_id ?? 1) === 2)>Inactivo</option>
                            </select>
                            @error('estatus_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="ip-form-actions">
                            <a href="{{ route('alumnos.index') }}" class="btn ip-btn-outline">Cancelar</a>
                            <button type="submit" class="btn ip-btn-success">
                                <i class="bi bi-check-lg me-1"></i>{{ $editing ? 'Actualizar' : 'Guardar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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

            const checkboxes = document.querySelectorAll('.js-na-checkbox');
            if (checkboxes.length) {
                function sync(cb) {
                    const input = document.getElementById(cb.dataset.naTarget);
                    if (!input) return;
                    input.disabled = cb.checked;
                }

                checkboxes.forEach(function (cb) {
                    sync(cb);
                    cb.addEventListener('change', function () {
                        sync(cb);
                    });
                });
            }

            const modal = document.getElementById('archivoModal');
            if (modal) {
                const img = document.getElementById('archivoModalImg');
                const title = document.getElementById('archivoModalLabel');

                modal.addEventListener('show.bs.modal', function (event) {
                    const trigger = event.relatedTarget;
                    img.src = trigger.getAttribute('data-bs-src');
                    title.textContent = trigger.getAttribute('data-bs-title');
                });
            }

            const archivosInput = document.getElementById('archivos');
            const previewContainer = document.getElementById('archivos-preview');

            if (archivosInput && previewContainer) {
                archivosInput.addEventListener('change', function () {
                    previewContainer.innerHTML = '';

                    Array.from(this.files || []).forEach(function (file) {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'text-center';

                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = function (e) {
                                const img = document.createElement('img');
                                img.src = e.target.result;
                                img.alt = file.name;
                                img.className = 'img-thumbnail ip-file-thumb d-block mb-1';
                                wrapper.appendChild(img);
                            };
                            reader.readAsDataURL(file);
                        } else {
                            const icon = document.createElement('div');
                            icon.className = 'ip-doc-thumb mb-1';
                            icon.innerHTML = '<i class="bi bi-file-earmark-text ip-doc-icon"></i>';
                            wrapper.appendChild(icon);
                        }

                        const nombre = document.createElement('div');
                        nombre.textContent = file.name;
                        nombre.className = 'small ip-muted text-truncate ip-file-name';
                        wrapper.appendChild(nombre);

                        previewContainer.appendChild(wrapper);
                    });
                });
            }
        })();
    </script>
@endpush
