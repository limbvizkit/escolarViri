@php
    $alumno = $alumno ?? null;
    $editing = isset($alumno);
    $title = $editing ? 'Editar alumno' : 'Nuevo alumno';
    $action = $editing ? route('alumnos.update', $alumno) : route('alumnos.store');
    $method = $editing ? 'PUT' : 'POST';
    $presetGradoEscolar = request()->query('grado_escolar_id', $alumno->grado_escolar_id ?? '');
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
                            <div class="col-md-6">
                                @if ($editing && $alumno->archivo)
                                    @php
                                        $extArchivo = strtolower(pathinfo($alumno->archivo, PATHINFO_EXTENSION));
                                        $esImagen = in_array($extArchivo, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
                                    @endphp
                                    <div class="mb-3">
                                        <label class="form-label d-block">Archivo actual</label>
                                        @if ($esImagen)
                                            <img id="archivo-preview" src="{{ Storage::url($alumno->archivo) }}"
                                                 alt="{{ basename($alumno->archivo) }}"
                                                 class="img-thumbnail ip-file-thumb d-block mb-2">
                                            <span class="ip-muted small d-block" id="archivo-nombre">{{ basename($alumno->archivo) }}</span>
                                        @else
                                            <a href="{{ Storage::url($alumno->archivo) }}" target="_blank"
                                               class="btn ip-btn-outline btn-sm mb-2">
                                                <i class="bi bi-file-earmark-text me-1"></i>{{ basename($alumno->archivo) }}
                                            </a>
                                            <img id="archivo-preview" class="d-none img-thumbnail ip-file-thumb d-block mb-2"
                                                 alt="Vista previa del nuevo archivo">
                                        @endif
                                    </div>
                                @else
                                    <img id="archivo-preview" class="d-none img-thumbnail ip-file-thumb d-block mb-2"
                                         alt="Vista previa del nuevo archivo">
                                @endif

                                <label for="archivo" class="form-label">Archivo</label>
                                <input type="file" id="archivo" name="archivo"
                                       class="form-control @error('archivo') is-invalid @enderror"
                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                @error('archivo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                <div class="form-text">PDF, JPG, PNG, DOC o DOCX. Tamaño máximo 5 MB.</div>
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
@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';

            const checkboxes = document.querySelectorAll('.js-na-checkbox');
            if (!checkboxes.length) return;

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

            const archivoInput = document.getElementById('archivo');
            if (archivoInput) {
                archivoInput.addEventListener('change', function () {
                    const file = this.files && this.files[0];
                    const preview = document.getElementById('archivo-preview');
                    if (!preview || !file || !file.type.startsWith('image/')) return;

                    const reader = new FileReader();
                    reader.onload = function (e) {
                        preview.src = e.target.result;
                        preview.classList.remove('d-none');
                        const nombre = document.getElementById('archivo-nombre');
                        if (nombre) nombre.textContent = file.name;
                    };
                    reader.readAsDataURL(file);
                });
            }
        })();
    </script>
@endpush