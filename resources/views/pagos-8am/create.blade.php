@php
    $pago8am = $pago8am ?? null;
    $editing = isset($pago8am);
    $title = $editing ? 'Editar pago 8 AM' : 'Nuevo pago 8 AM';
    $action = $editing ? route('pagos-8am.update', $pago8am) : route('pagos-8am.store');
    $method = $editing ? 'PUT' : 'POST';
    $registrosExistentes = $registrosExistentes ?? [];
    $formaPagoSeleccionada = (string) old('forma_pago_id', $pago8am->forma_pago_id ?? ($editing ? '' : $formasPago->firstWhere('nombre', 'Efectivo')?->id));
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Datos del pago 8 AM</h5>
                </div>

                <div class="ip-card-body">
                    <form action="{{ $action }}" method="POST" class="js-form-pago-8am">
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
                                                    {{ old('alumno_id', $pago8am->alumno_id ?? '') == $alumno->id ? 'selected' : '' }}>
                                                    {{ $alumno->nombre_completo }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('alumno_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-5">
                                <label for="mes" class="form-label">Mes y año <span class="ip-required">*</span></label>
                                <input type="month" id="mes" name="mes"
                                       class="form-control @error('mes') is-invalid @enderror"
                                       value="{{ old('mes', $pago8am->mes ?? '') }}" required>
                                @error('mes')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-5">
                                <label for="monto" class="form-label">Monto pagado <span class="ip-required">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0.01" id="monto" name="monto"
                                           class="form-control @error('monto') is-invalid @enderror"
                                           value="{{ old('monto', $pago8am->monto ?? '') }}" required>
                                </div>
                                @error('monto')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-7">
                                <label for="forma_pago_id" class="form-label">Forma de pago</label>
                                <select id="forma_pago_id" name="forma_pago_id"
                                        class="form-select @error('forma_pago_id') is-invalid @enderror">
                                    <option value="">— Sin forma —</option>
                                    @foreach ($formasPago as $forma)
                                        <option value="{{ $forma->id }}"
                                            {{ $formaPagoSeleccionada === (string) $forma->id ? 'selected' : '' }}>
                                            {{ $forma->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('forma_pago_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea id="observaciones" name="observaciones" rows="3" maxlength="1000"
                                      class="form-control @error('observaciones') is-invalid @enderror"
                                      placeholder="Notas adicionales sobre el pago (opcional)">{{ old('observaciones', $pago8am->observaciones ?? '') }}</textarea>
                            @error('observaciones')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="ip-form-actions">
                            <a href="{{ route('pagos-8am.index') }}" class="btn ip-btn-outline">Cancelar</a>
                            <button type="submit" class="btn ip-btn-success">
                                <i class="bi bi-check-lg me-1"></i>{{ $editing ? 'Actualizar' : 'Guardar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDuplicado" tabindex="-1" aria-labelledby="modalDuplicadoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDuplicadoLabel">Registro duplicado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">
                        Ya existe un registro para ese alumno en el mes seleccionado. ¿Desea guardarlo?
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn ip-btn-outline" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn ip-btn-success" id="btn-si-guardar-duplicado">Sí</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';

            const form = document.querySelector('form.js-form-pago-8am');
            const modalEl = document.getElementById('modalDuplicado');
            if (!form || !modalEl) return;

            const registrosExistentes = new Set(@json($registrosExistentes));
            const modal = new bootstrap.Modal(modalEl);
            let confirmado = false;

            form.addEventListener('submit', function (e) {
                if (confirmado) return;

                const alumno = form.querySelector('#alumno_id').value;
                const mes = form.querySelector('#mes').value;
                if (!alumno || !mes) return;

                if (registrosExistentes.has(alumno + '|' + mes)) {
                    e.preventDefault();
                    modal.show();
                }
            });

            document.getElementById('btn-si-guardar-duplicado').addEventListener('click', function () {
                confirmado = true;
                modal.hide();
                form.requestSubmit();
            });
        })();
    </script>
@endpush
