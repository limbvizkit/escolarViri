@php
    $taller = $taller ?? null;
    $editing = isset($taller);
    $title = $editing ? 'Editar taller' : 'Nuevo taller';
    $action = $editing ? route('talleres.update', $taller) : route('talleres.store');
    $method = $editing ? 'PUT' : 'POST';
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Datos del taller</h5>
                </div>

                <div class="ip-card-body">
                    <form action="{{ $action }}" method="POST">
                        @csrf
                        @method($method)

                        <div class="row g-3 mb-4">
                            <div class="col-md-7">
                                <label for="nombre" class="form-label">Nombre del taller <span class="ip-required">*</span></label>
                                <input type="text" id="nombre" name="nombre"
                                       class="form-control @error('nombre') is-invalid @enderror"
                                       value="{{ old('nombre', $taller->nombre ?? '') }}" required>
                                @error('nombre')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-5">
                                <label for="costo" class="form-label">Costo <span class="ip-required">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0" id="costo" name="costo"
                                           class="form-control @error('costo') is-invalid @enderror"
                                           value="{{ old('costo', $taller->costo ?? '') }}" required>
                                </div>
                                @error('costo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="ip-form-actions">
                            <a href="{{ route('talleres.index') }}" class="btn ip-btn-outline">Cancelar</a>
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