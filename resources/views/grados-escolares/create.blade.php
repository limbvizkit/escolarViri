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
                            <label for="estatus_id" class="form-label d-block">Estatus</label>
                            <select id="estatus_id" name="estatus_id"
                                    class="form-select @error('estatus_id') is-invalid @enderror">
                                <option value="1" @selected((int) old('estatus_id', $gradoEscolar->estatus_id ?? 1) === 1)>Activo</option>
                                <option value="2" @selected((int) old('estatus_id', $gradoEscolar->estatus_id ?? 1) === 2)>Inactivo</option>
                            </select>
                            @error('estatus_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
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