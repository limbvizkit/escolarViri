@php
    $editing = isset($empleado);
    $title = $editing ? 'Editar empleado' : 'Nuevo empleado';
    $action = $editing ? route('empleados.update', $empleado) : route('empleados.store');
    $method = $editing ? 'PUT' : 'POST';
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Datos del empleado</h5>
                </div>

                <div class="ip-card-body">
                    <form action="{{ $action }}" method="POST">
                        @csrf
                        @method($method)

                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label for="sucursal_id" class="form-label">Sucursal <span class="ip-required">*</span></label>
                                <select id="sucursal_id" name="sucursal_id"
                                        class="form-select @error('sucursal_id') is-invalid @enderror" required>
                                    <option value="">— Seleccionar sucursal —</option>
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id }}"
                                            {{ old('sucursal_id', $empleado->sucursal_id ?? '') == $sucursal->id ? 'selected' : '' }}>
                                            {{ $sucursal->nombre }} · {{ $sucursal->escuela->nombre ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('sucursal_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="nombre" class="form-label">Nombre <span class="ip-required">*</span></label>
                                <input type="text" id="nombre" name="nombre"
                                       class="form-control @error('nombre') is-invalid @enderror"
                                       value="{{ old('nombre', $empleado->nombre ?? '') }}" required>
                                @error('nombre')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="apellido_paterno" class="form-label">Apellido paterno <span class="ip-required">*</span></label>
                                <input type="text" id="apellido_paterno" name="apellido_paterno"
                                       class="form-control @error('apellido_paterno') is-invalid @enderror"
                                       value="{{ old('apellido_paterno', $empleado->apellido_paterno ?? '') }}" required>
                                @error('apellido_paterno')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="apellido_materno" class="form-label">Apellido materno</label>
                                <input type="text" id="apellido_materno" name="apellido_materno"
                                       class="form-control @error('apellido_materno') is-invalid @enderror"
                                       value="{{ old('apellido_materno', $empleado->apellido_materno ?? '') }}">
                                @error('apellido_materno')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="puesto" class="form-label">Área / Puesto</label>
                                <input type="text" id="puesto" name="puesto"
                                       class="form-control @error('puesto') is-invalid @enderror"
                                       value="{{ old('puesto', $empleado->puesto ?? '') }}"
                                       placeholder="Ej. Dirección General, Maestras">
                                @error('puesto')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="horario" class="form-label">Horario</label>
                                <input type="text" id="horario" name="horario"
                                       class="form-control @error('horario') is-invalid @enderror"
                                       value="{{ old('horario', $empleado->horario ?? '') }}"
                                       placeholder="Ej. Lunes a Viernes 8:00 - 14:30">
                                @error('horario')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <input type="email" id="email" name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $empleado->email ?? '') }}">
                                @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" id="telefono" name="telefono"
                                       class="form-control @error('telefono') is-invalid @enderror"
                                       value="{{ old('telefono', $empleado->telefono ?? '') }}">
                                @error('telefono')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="telefono_personal" class="form-label">Teléfono personal</label>
                                <input type="text" id="telefono_personal" name="telefono_personal"
                                       class="form-control @error('telefono_personal') is-invalid @enderror"
                                       value="{{ old('telefono_personal', $empleado->telefono_personal ?? '') }}">
                                @error('telefono_personal')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento</label>
                                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                                       class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                                       value="{{ old('fecha_nacimiento', isset($empleado) && $empleado->fecha_nacimiento ? $empleado->fecha_nacimiento->format('Y-m-d') : '') }}">
                                @error('fecha_nacimiento')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="tipo_sangre" class="form-label">Tipo de sangre</label>
                                <input type="text" id="tipo_sangre" name="tipo_sangre"
                                       class="form-control @error('tipo_sangre') is-invalid @enderror"
                                       value="{{ old('tipo_sangre', $empleado->tipo_sangre ?? '') }}">
                                @error('tipo_sangre')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="curp" class="form-label">CURP</label>
                                <input type="text" id="curp" name="curp"
                                       class="form-control @error('curp') is-invalid @enderror"
                                       value="{{ old('curp', $empleado->curp ?? '') }}">
                                @error('curp')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label for="direccion" class="form-label">Dirección</label>
                                <textarea id="direccion" name="direccion" rows="2"
                                          class="form-control @error('direccion') is-invalid @enderror">{{ old('direccion', $empleado->direccion ?? '') }}</textarea>
                                @error('direccion')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="numeros_emergencia" class="form-label">Números de emergencias</label>
                                <textarea id="numeros_emergencia" name="numeros_emergencia" rows="3"
                                          class="form-control @error('numeros_emergencia') is-invalid @enderror">{{ old('numeros_emergencia', $empleado->numeros_emergencia ?? '') }}</textarea>
                                @error('numeros_emergencia')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="enfermedad" class="form-label">Enfermedad</label>
                                <textarea id="enfermedad" name="enfermedad" rows="3"
                                          class="form-control @error('enfermedad') is-invalid @enderror">{{ old('enfermedad', $empleado->enfermedad ?? '') }}</textarea>
                                @error('enfermedad')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="alergias" class="form-label">Alergias</label>
                                <textarea id="alergias" name="alergias" rows="3"
                                          class="form-control @error('alergias') is-invalid @enderror">{{ old('alergias', $empleado->alergias ?? '') }}</textarea>
                                @error('alergias')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label for="medicamento" class="form-label">Medicamento</label>
                                <textarea id="medicamento" name="medicamento" rows="2"
                                          class="form-control @error('medicamento') is-invalid @enderror">{{ old('medicamento', $empleado->medicamento ?? '') }}</textarea>
                                @error('medicamento')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="estatus_id" class="form-label d-block">Estatus</label>
                                <select id="estatus_id" name="estatus_id"
                                        class="form-select @error('estatus_id') is-invalid @enderror">
                                    <option value="1" @selected((int) old('estatus_id', $empleado->estatus_id ?? 1) === 1)>Activo</option>
                                    <option value="2" @selected((int) old('estatus_id', $empleado->estatus_id ?? 1) === 2)>Inactivo</option>
                                </select>
                                @error('estatus_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="ip-form-actions">
                            <a href="{{ route('empleados.index') }}" class="btn ip-btn-outline">Cancelar</a>
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
