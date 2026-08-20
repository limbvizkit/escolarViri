@extends('layouts.app')

@section('title', 'Nuevo adeudo')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Datos del adeudo</h5>
                </div>

                <div class="ip-card-body">
                    <form action="{{ route('adeudos.store') }}" method="POST">
                        @csrf

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
                                                    {{ old('alumno_id') == $alumno->id ? 'selected' : '' }}>
                                                    {{ $alumno->nombre_completo }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('alumno_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-5">
                                <label for="estatus" class="form-label">Estatus</label>
                                <select id="estatus" name="estatus"
                                        class="form-select @error('estatus') is-invalid @enderror">
                                    <option value="pendiente" @selected(old('estatus', 'pendiente') === 'pendiente')>Pendiente</option>
                                    <option value="pagado" @selected(old('estatus') === 'pagado')>Pagado</option>
                                </select>
                                @error('estatus')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label for="concepto" class="form-label">Concepto <span class="ip-required">*</span></label>
                                <input type="text" id="concepto" name="concepto" maxlength="255"
                                       class="form-control @error('concepto') is-invalid @enderror"
                                       value="{{ old('concepto') }}" required>
                                @error('concepto')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="monto" class="form-label">Monto <span class="ip-required">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0.01" id="monto" name="monto"
                                           class="form-control @error('monto') is-invalid @enderror"
                                           value="{{ old('monto') }}" required>
                                </div>
                                @error('monto')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="anotaciones" class="form-label">Anotaciones</label>
                            <textarea id="anotaciones" name="anotaciones" rows="3" maxlength="1000"
                                      class="form-control @error('anotaciones') is-invalid @enderror"
                                      placeholder="Notas adicionales sobre el adeudo (opcional)">{{ old('anotaciones') }}</textarea>
                            @error('anotaciones')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="ip-form-actions">
                            <a href="{{ route('adeudos.index') }}" class="btn ip-btn-outline">Cancelar</a>
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