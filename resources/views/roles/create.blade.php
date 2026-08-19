@php
    $editing = isset($rol);
    $title = $editing ? 'Editar rol' : 'Nuevo rol';
    $action = $editing ? route('roles.update', $rol) : route('roles.store');
    $method = $editing ? 'PUT' : 'POST';
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Datos del rol</h5>
                </div>

                <div class="ip-card-body">
                    <form action="{{ $action }}" method="POST">
                        @csrf
                        @method($method)

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre <span class="ip-required">*</span></label>
                            <input type="text" id="nombre" name="nombre"
                                   class="form-control @error('nombre') is-invalid @enderror"
                                   value="{{ old('nombre', $rol->nombre ?? '') }}" required>
                            @error('nombre')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" id="slug" name="slug"
                                   class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug', $rol->slug ?? '') }}"
                                   placeholder="Se genera automáticamente">
                            @error('slug')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            <div class="form-text">Identificador único en minúsculas y guiones. Vacío = automático.</div>
                        </div>

                        <div class="ip-form-actions">
                            <a href="{{ route('roles.index') }}" class="btn ip-btn-outline">Cancelar</a>
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