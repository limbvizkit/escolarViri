@php
    $editing = isset($sucursal);
    $title = $editing ? 'Editar sucursal' : 'Nueva sucursal';
    $action = $editing ? route('sucursales.update', $sucursal) : route('sucursales.store');
    $method = $editing ? 'PUT' : 'POST';
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Datos de la sucursal</h5>
                </div>

                <div class="ip-card-body">
                    <form action="{{ $action }}" method="POST">
                        @csrf
                        @method($method)

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="escuela_id" class="form-label">Escuela <span class="ip-required">*</span></label>
                                <select id="escuela_id" name="escuela_id"
                                        class="form-select @error('escuela_id') is-invalid @enderror" required>
                                    <option value="">— Seleccionar escuela —</option>
                                    @foreach ($escuelas as $escuela)
                                        <option value="{{ $escuela->id }}"
                                            {{ old('escuela_id', $sucursal->escuela_id ?? '') == $escuela->id ? 'selected' : '' }}>
                                            {{ $escuela->nombre }} ({{ $escuela->clave }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('escuela_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="nombre" class="form-label">Nombre <span class="ip-required">*</span></label>
                                <input type="text" id="nombre" name="nombre"
                                       class="form-control @error('nombre') is-invalid @enderror"
                                       value="{{ old('nombre', $sucursal->nombre ?? '') }}" required>
                                @error('nombre')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label for="direccion" class="form-label">Dirección</label>
                                <input type="text" id="direccion" name="direccion"
                                       class="form-control @error('direccion') is-invalid @enderror"
                                       value="{{ old('direccion', $sucursal->direccion ?? '') }}">
                                @error('direccion')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" id="telefono" name="telefono"
                                       class="form-control @error('telefono') is-invalid @enderror"
                                       value="{{ old('telefono', $sucursal->telefono ?? '') }}">
                                @error('telefono')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <input type="email" id="email" name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $sucursal->email ?? '') }}">
                                @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="estatus_id" class="form-label d-block">Estatus</label>
                                <select id="estatus_id" name="estatus_id"
                                        class="form-select @error('estatus_id') is-invalid @enderror">
                                    <option value="1" @selected((int) old('estatus_id', $sucursal->estatus_id ?? 1) === 1)>Activa</option>
                                    <option value="2" @selected((int) old('estatus_id', $sucursal->estatus_id ?? 1) === 2)>Inactiva</option>
                                </select>
                                @error('estatus_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="ip-form-actions">
                            <a href="{{ route('sucursales.index') }}" class="btn ip-btn-outline">Cancelar</a>
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