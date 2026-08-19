@php
    $editing = isset($usuario);
    $title = $editing ? 'Editar usuario' : 'Nuevo usuario';
    $action = $editing ? route('usuarios.update', $usuario) : route('usuarios.store');
    $method = $editing ? 'PUT' : 'POST';
    $presetEmpleado = request()->query('empleado_id', $usuario->empleado_id ?? '');
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Datos del usuario</h5>
                </div>

                <div class="ip-card-body">
                    <form action="{{ $action }}" method="POST">
                        @csrf
                        @method($method)

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nombre <span class="ip-required">*</span></label>
                                <input type="text" id="name" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $usuario->name ?? '') }}" required>
                                @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Correo electrónico <span class="ip-required">*</span></label>
                                <input type="email" id="email" name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $usuario->email ?? '') }}" required>
                                @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="role_id" class="form-label">Rol</label>
                                <select id="role_id" name="role_id"
                                        class="form-select @error('role_id') is-invalid @enderror">
                                    <option value="">— Sin rol —</option>
                                    @foreach ($roles as $rol)
                                        <option value="{{ $rol->id }}"
                                            {{ old('role_id', $usuario->role_id ?? '') == $rol->id ? 'selected' : '' }}>
                                            {{ $rol->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="empleado_id" class="form-label">Empleado</label>
                                <select id="empleado_id" name="empleado_id"
                                        class="form-select @error('empleado_id') is-invalid @enderror">
                                    <option value="">— Sin empleado —</option>
                                    @foreach ($empleados as $empleado)
                                        <option value="{{ $empleado->id }}"
                                            {{ old('empleado_id', $presetEmpleado) == $empleado->id ? 'selected' : '' }}>
                                            {{ $empleado->nombre_completo }} · {{ $empleado->sucursal->nombre ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('empleado_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                @if ($editing)
                                    <div class="form-text">Empleados con usuario ya asignado se ocultan al crear.</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label">
                                    Contraseña @if (!$editing)<span class="ip-required">*</span>@endif
                                </label>
                                <input type="password" id="password" name="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       @if (!$editing) required @endif
                                       autocomplete="new-password">
                                @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                @if ($editing)
                                    <div class="form-text">Dejar vacío para conservar la actual.</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                       class="form-control"
                                       autocomplete="new-password">
                            </div>
                        </div>

                        <div class="ip-form-actions">
                            <a href="{{ route('usuarios.index') }}" class="btn ip-btn-outline">Cancelar</a>
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