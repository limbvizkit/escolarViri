@php
    $pagoTaller = $pagoTaller ?? null;
    $editing = isset($pagoTaller);
    $title = $editing ? 'Editar pago de taller' : 'Nuevo pago de taller';
    $action = $editing ? route('pagos-talleres.update', $pagoTaller) : route('pagos-talleres.store');
    $method = $editing ? 'PUT' : 'POST';

    $alumnoSeleccionado = (string) old('alumno_id', $pagoTaller->alumno_id ?? '');
    $tallerSeleccionado = (string) old('taller_id', $pagoTaller->taller_id ?? '');
    $talleresSeleccionables = $talleresPorAlumno[$alumnoSeleccionado] ?? [];
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Datos del pago de taller</h5>
                </div>

                <div class="ip-card-body">
                    <form action="{{ $action }}" method="POST">
                        @csrf
                        @method($method)

                        <div class="row g-3 mb-4">
                            <div class="col-md-7">
                                <label for="alumno_id" class="form-label">Alumno <span class="ip-required">*</span></label>
                                <select id="alumno_id" name="alumno_id"
                                        class="form-select @error('alumno_id') is-invalid @enderror" required>
                                    <option value="">— Seleccionar alumno —</option>
                                    @foreach ($alumnos->groupBy(fn ($alumno) => $alumno->gradoEscolar->nombre ?? 'Sin grado escolar') as $grupo => $lista)
                                        <optgroup label="{{ $grupo }}">
                                            @foreach ($lista as $alumno)
                                                <option value="{{ $alumno->id }}"
                                                    {{ $alumnoSeleccionado === (string) $alumno->id ? 'selected' : '' }}>
                                                    {{ $alumno->nombre_completo }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('alumno_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-5">
                                <label for="taller_id" class="form-label">Taller <span class="ip-required">*</span></label>
                                <select id="taller_id" name="taller_id"
                                        class="form-select @error('taller_id') is-invalid @enderror" required>
                                    <option value="">— Seleccionar taller —</option>
                                    @foreach ($talleresSeleccionables as $taller)
                                        <option value="{{ $taller['id'] }}"
                                            {{ $tallerSeleccionado === (string) $taller['id'] ? 'selected' : '' }}>
                                            {{ $taller['nombre'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('taller_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                <div class="form-text" id="taller-hint">
                                    Solo se listan los talleres en los que el alumno está inscrito.
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-5">
                                <label for="mes" class="form-label">Mes y año <span class="ip-required">*</span></label>
                                <input type="month" id="mes" name="mes"
                                       class="form-control @error('mes') is-invalid @enderror"
                                       value="{{ old('mes', $pagoTaller->mes ?? '') }}" required>
                                @error('mes')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-7">
                                <label for="monto" class="form-label">Monto pagado <span class="ip-required">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0.01" id="monto" name="monto"
                                           class="form-control @error('monto') is-invalid @enderror"
                                           value="{{ old('monto', $pagoTaller->monto ?? '') }}" required>
                                </div>
                                @error('monto')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea id="observaciones" name="observaciones" rows="3" maxlength="1000"
                                      class="form-control @error('observaciones') is-invalid @enderror"
                                      placeholder="Notas adicionales sobre el pago (opcional)">{{ old('observaciones', $pagoTaller->observaciones ?? '') }}</textarea>
                            @error('observaciones')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="ip-form-actions">
                            <a href="{{ route('pagos-talleres.index') }}" class="btn ip-btn-outline">Cancelar</a>
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

            const talleresPorAlumno = @json($talleresPorAlumno);
            const alumnoSelect = document.getElementById('alumno_id');
            const tallerSelect = document.getElementById('taller_id');
            const hint = document.getElementById('taller-hint');

            if (!alumnoSelect || !tallerSelect) return;

            const hintDefault = hint ? hint.textContent.trim() : '';

            function render(opciones) {
                tallerSelect.innerHTML = '';

                const vacio = document.createElement('option');
                vacio.value = '';
                vacio.textContent = '— Seleccionar taller —';
                tallerSelect.appendChild(vacio);

                opciones.forEach(function (taller) {
                    const opt = document.createElement('option');
                    opt.value = taller.id;
                    opt.textContent = taller.nombre;
                    tallerSelect.appendChild(opt);
                });
            }

            alumnoSelect.addEventListener('change', function () {
                const opciones = talleresPorAlumno[String(alumnoSelect.value)] || [];
                render(opciones);

                if (hint) {
                    hint.textContent = (alumnoSelect.value && opciones.length === 0)
                        ? 'El alumno seleccionado no tiene talleres inscritos.'
                        : hintDefault;
                }
            });
        })();
    </script>
@endpush
