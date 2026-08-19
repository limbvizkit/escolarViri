@php
    $editing = isset($gradoEscolar);
    $title = $editing ? 'Editar grado escolar' : 'Nuevo grado escolar';
    $action = $editing ? route('grados-escolares.update', $gradoEscolar) : route('grados-escolares.store');
    $method = $editing ? 'PUT' : 'POST';
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Datos del grado escolar</h5>
                </div>

                <div class="ip-card-body">
                    <form action="{{ $action }}" method="POST">
                        @csrf
                        @method($method)

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del grado escolar <span class="ip-required">*</span></label>
                            <input type="text" id="nombre" name="nombre"
                                   class="form-control @error('nombre') is-invalid @enderror"
                                   value="{{ old('nombre', $gradoEscolar->nombre ?? '') }}" required>
                            @error('nombre')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            <div class="form-text">Ej. ESTIMULACIÓN TEMPRANA, MATERNAL, KINDER 1...</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block">Estatus</label>
                            <div class="form-check form-switch mt-2">
                                <input type="hidden" name="estatus" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="estatus"
                                       name="estatus" value="1"
                                       {{ old('estatus', $gradoEscolar->estatus ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="estatus">Activo</label>
                            </div>
                        </div>

                        <div class="ip-form-actions">
                            <a href="{{ route('grados-escolares.index') }}" class="btn ip-btn-outline">Cancelar</a>
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