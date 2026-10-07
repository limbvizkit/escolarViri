@php
    $pago = $pago ?? null;
    $editing = isset($pago);
    $title = $editing ? 'Editar pago' : 'Nuevo pago';
    $action = $editing ? route('pagos.update', $pago) : route('pagos.store');
    $method = $editing ? 'PUT' : 'POST';
    $montos = [
        'entrada_8am' => 'Entrada 8 AM',
        'pronto_pago' => 'Pronto pago',
        'pago_normal' => 'Pago normal',
        'talleres' => 'Talleres',
        'lunch' => 'Lunch',
        'horario_extendido' => 'Horario extendido',
    ];
    $montosAnuales = [
        'inscripcion' => 'Inscripción',
        'reinscripcion' => 'Re/Inscripción',
        'materiales' => 'Materiales',
        'entrevista' => 'Entrevista',
        'natgeo' => 'NatGeo',
        'fotos' => 'Fotos',
        'cursos' => 'Cursos',
    ];

    // Campos que se capturan en sus respectivos módulos y quedan bloqueados
    // aquí para no romper la consistencia.
    $camposBloqueados = ['talleres', 'lunch', 'horario_extendido', 'cursos'];
    $bloqueado = fn (string $campo): bool => in_array($campo, $camposBloqueados, true);
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Datos del pago</h5>
                </div>

                <div class="ip-card-body">
                    <form action="{{ $action }}" method="POST">
                        @csrf
                        @method($method)

                        {{-- Alumno y mes --}}
                        <h6 class="fw-semibold text-uppercase small text-secondary mb-3">
                            <i class="bi bi-person-vcard me-1"></i>Alumno y periodo
                        </h6>
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
                                                    {{ old('alumno_id', $pago->alumno_id ?? '') == $alumno->id ? 'selected' : '' }}>
                                                    {{ $alumno->nombre_completo }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('alumno_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-5">
                                <label for="mes" class="form-label">Mes del pago <span class="ip-required">*</span></label>
                                <input type="month" id="mes" name="mes"
                                       class="form-control @error('mes') is-invalid @enderror"
                                       value="{{ old('mes', $pago->mes ?? '') }}" required>
                                @error('mes')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Fecha y forma de pago --}}
                        <h6 class="fw-semibold text-uppercase small text-secondary mb-3">
                            <i class="bi bi-calendar-check me-1"></i>Fechas y forma de pago
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="fecha" class="form-label">Fecha de pago</label>
                                <input type="date" id="fecha" name="fecha"
                                       class="form-control @error('fecha') is-invalid @enderror"
                                       value="{{ old('fecha', $pago?->fecha?->format('Y-m-d') ?? '') }}">
                                @error('fecha')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="forma_pago_id" class="form-label">Forma de pago</label>
                                <select id="forma_pago_id" name="forma_pago_id"
                                        class="form-select @error('forma_pago_id') is-invalid @enderror">
                                    <option value="">— Sin forma —</option>
                                    @foreach ($formasPago as $forma)
                                        <option value="{{ $forma->id }}"
                                            {{ old('forma_pago_id', $pago->forma_pago_id ?? '') == $forma->id ? 'selected' : '' }}>
                                            {{ $forma->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('forma_pago_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Montos --}}
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-semibold text-uppercase small text-secondary mb-0">
                                <i class="bi bi-cash-coin me-1"></i>Importes del mes
                            </h6>
                            <button type="button" id="btn-desbloquear-campos" class="btn ip-btn-outline btn-sm">
                                <i class="bi bi-lock-fill me-1"></i>Desbloquear campos
                            </button>
                        </div>
                        <div class="form-text mb-3">
                            Talleres, Lunch, Horario extendido y Cursos se capturan en sus respectivos módulos y están bloqueados aquí para evitar inconsistencias.
                        </div>
                        <div class="row g-3 mb-4">
                            @foreach ($montos as $campo => $etiqueta)
                                <div class="col-md-3">
                                    <label for="{{ $campo }}" class="form-label">
                                        {{ $etiqueta }}
                                        @if ($bloqueado($campo))<i class="bi bi-lock-fill text-secondary ms-1" title="Bloqueado"></i>@endif
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" min="0" id="{{ $campo }}" name="{{ $campo }}"
                                               class="form-control @error($campo) is-invalid @enderror"
                                               value="{{ old($campo, $pago->$campo ?? '') }}"
                                               @disabled($bloqueado($campo)) @if ($bloqueado($campo)) data-bloqueado="1" @endif>
                                    </div>
                                    @error($campo)<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                            @endforeach
                        </div>

                        {{-- Importes anuales o extraordinarios --}}
                        <h6 class="fw-semibold text-uppercase small text-secondary mb-3">
                            <i class="bi bi-calendar2-range me-1"></i>Importes anuales o extraordinarios
                        </h6>
                        <div class="row g-3 mb-4">
                            @foreach ($montosAnuales as $campo => $etiqueta)
                                <div class="col-md-3">
                                    <label for="{{ $campo }}" class="form-label">
                                        {{ $etiqueta }}
                                        @if ($bloqueado($campo))<i class="bi bi-lock-fill text-secondary ms-1" title="Bloqueado"></i>@endif
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" min="0" id="{{ $campo }}" name="{{ $campo }}"
                                               class="form-control @error($campo) is-invalid @enderror"
                                               value="{{ old($campo, $pago->$campo ?? '') }}"
                                               @disabled($bloqueado($campo)) @if ($bloqueado($campo)) data-bloqueado="1" @endif>
                                    </div>
                                    @error($campo)<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                            @endforeach
                        </div>

                        <div class="ip-form-actions">
                            <a href="{{ route('pagos.index') }}" class="btn ip-btn-outline">Cancelar</a>
                            <button type="submit" class="btn ip-btn-success">
                                <i class="bi bi-check-lg me-1"></i>{{ $editing ? 'Actualizar' : 'Guardar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDesbloquearCampos" tabindex="-1" aria-labelledby="modalDesbloquearCamposLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDesbloquearCamposLabel">Desbloquear campos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">
                        Al modificar estos datos se perderá la consistencia de los mismos, favor de capturarlos en sus respectivos módulos. ¿Desea desbloquear los campos?
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn ip-btn-outline" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn ip-btn-success" id="btn-si-desbloquear">Sí</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';

            const boton = document.getElementById('btn-desbloquear-campos');
            const modalEl = document.getElementById('modalDesbloquearCampos');
            if (!boton || !modalEl) return;

            const modal = new bootstrap.Modal(modalEl);

            boton.addEventListener('click', function () {
                modal.show();
            });

            document.getElementById('btn-si-desbloquear').addEventListener('click', function () {
                document.querySelectorAll('[data-bloqueado="1"]').forEach(function (campo) {
                    campo.disabled = false;
                    campo.removeAttribute('data-bloqueado');
                });

                boton.disabled = true;
                boton.classList.remove('ip-btn-outline');
                boton.classList.add('ip-btn-success');
                boton.innerHTML = '<i class="bi bi-unlock-fill me-1"></i>Campos desbloqueados';

                modal.hide();
            });
        })();
    </script>
@endpush