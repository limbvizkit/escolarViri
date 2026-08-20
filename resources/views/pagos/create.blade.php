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
    ];
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
                        <h6 class="fw-semibold text-uppercase small text-secondary mb-3">
                            <i class="bi bi-cash-coin me-1"></i>Importes del mes
                        </h6>
                        <div class="row g-3 mb-4">
                            @foreach ($montos as $campo => $etiqueta)
                                <div class="col-md-3">
                                    <label for="{{ $campo }}" class="form-label">{{ $etiqueta }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" min="0" id="{{ $campo }}" name="{{ $campo }}"
                                               class="form-control @error($campo) is-invalid @enderror"
                                               value="{{ old($campo, $pago->$campo ?? '') }}">
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
@endsection