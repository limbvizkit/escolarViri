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
        <div class="col-lg-9">
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
                                <label for="puesto" class="form-label">Puesto</label>
                                <input type="text" id="puesto" name="puesto"
                                       class="form-control @error('puesto') is-invalid @enderror"
                                       value="{{ old('puesto', $empleado->puesto ?? '') }}"
                                       placeholder="Ej. Profesor, Coordinador">
                                @error('puesto')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
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
                                <label class="form-label d-block">Estatus</label>
                                <div class="form-check form-switch mt-2">
                                    <input type="hidden" name="estatus" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="estatus"
                                           name="estatus" value="1"
                                           {{ old('estatus', $empleado->estatus ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="estatus">Activo</label>
                                </div>
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