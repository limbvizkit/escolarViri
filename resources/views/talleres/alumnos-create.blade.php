@extends('layouts.app')

@section('title', 'Agregar alumno a taller')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Agregar alumno a: {{ $taller->nombre }}</h5>
                </div>

                <div class="ip-card-body">
                    <form action="{{ route('talleres.alumnos.store', $taller) }}" method="POST">
                        @csrf

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="alumno_id" class="form-label">Alumno <span class="ip-required">*</span></label>
                                <select id="alumno_id" name="alumno_id"
                                        class="form-select @error('alumno_id') is-invalid @enderror" required>
                                    <option value="">— Seleccionar alumno —</option>
                                    @forelse ($alumnosDisponibles as $alumno)
                                        <option value="{{ $alumno->id }}" {{ old('alumno_id') == $alumno->id ? 'selected' : '' }}>
                                            {{ $alumno->nombre_completo }} — {{ $alumno->gradoEscolar->nombre ?? 'Sin grado escolar' }}
                                        </option>
                                    @empty
                                        <option value="" disabled>Todos los alumnos activos ya están inscritos en este taller.</option>
                                    @endforelse
                                </select>
                                @error('alumno_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label for="hora_inicio" class="form-label">Hora inicio <span class="ip-required">*</span></label>
                                <input type="time" id="hora_inicio" name="hora_inicio"
                                       class="form-control @error('hora_inicio') is-invalid @enderror"
                                       value="{{ old('hora_inicio') }}" required>
                                @error('hora_inicio')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label for="hora_fin" class="form-label">Hora fin <span class="ip-required">*</span></label>
                                <input type="time" id="hora_fin" name="hora_fin"
                                       class="form-control @error('hora_fin') is-invalid @enderror"
                                       value="{{ old('hora_fin') }}" required>
                                @error('hora_fin')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="monto_pagado" class="form-label">Monto pagado</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0" id="monto_pagado" name="monto_pagado"
                                           class="form-control @error('monto_pagado') is-invalid @enderror"
                                           value="{{ old('monto_pagado') }}">
                                </div>
                                @error('monto_pagado')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                <div class="form-text">Opcional; puede ajustarse después en la tabla.</div>
                            </div>
                        </div>

                        <div class="ip-form-actions">
                            <a href="{{ route('talleres.index') }}" class="btn ip-btn-outline">Cancelar</a>
                            <button type="submit" class="btn ip-btn-success">
                                <i class="bi bi-check-lg me-1"></i>Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection