@php
    $curso = $curso ?? null;
    $editing = isset($curso);
    $title = $editing ? 'Editar curso' : 'Nuevo curso';
    $action = $editing ? route('cursos.update', $curso) : route('cursos.store');
    $method = $editing ? 'PUT' : 'POST';
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Datos del curso</h5>
                </div>

                <div class="ip-card-body">
                    <form action="{{ $action }}" method="POST">
                        @csrf
                        @method($method)

                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label for="nombre" class="form-label">Nombre del curso <span class="ip-required">*</span></label>
                                <input type="text" id="nombre" name="nombre"
                                       class="form-control @error('nombre') is-invalid @enderror"
                                       value="{{ old('nombre', $curso->nombre ?? '') }}" required>
                                @error('nombre')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="costo" class="form-label">Costo <span class="ip-required">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0" id="costo" name="costo"
                                           class="form-control @error('costo') is-invalid @enderror"
                                           value="{{ old('costo', $curso->costo ?? '') }}" required>
                                </div>
                                @error('costo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label for="fecha_inicio" class="form-label">Fecha inicio</label>
                                <input type="date" id="fecha_inicio" name="fecha_inicio"
                                       class="form-control @error('fecha_inicio') is-invalid @enderror"
                                       value="{{ old('fecha_inicio', $curso?->fecha_inicio?->format('Y-m-d') ?? '') }}">
                                @error('fecha_inicio')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label for="fecha_fin" class="form-label">Fecha fin</label>
                                <input type="date" id="fecha_fin" name="fecha_fin"
                                       class="form-control @error('fecha_fin') is-invalid @enderror"
                                       value="{{ old('fecha_fin', $curso?->fecha_fin?->format('Y-m-d') ?? '') }}">
                                @error('fecha_fin')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label for="hora_inicio" class="form-label">Hora inicio</label>
                                <input type="time" id="hora_inicio" name="hora_inicio"
                                       class="form-control @error('hora_inicio') is-invalid @enderror"
                                       value="{{ old('hora_inicio', substr((string) ($curso->hora_inicio ?? ''), 0, 5)) }}">
                                @error('hora_inicio')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label for="hora_fin" class="form-label">Hora fin</label>
                                <input type="time" id="hora_fin" name="hora_fin"
                                       class="form-control @error('hora_fin') is-invalid @enderror"
                                       value="{{ old('hora_fin', substr((string) ($curso->hora_fin ?? ''), 0, 5)) }}">
                                @error('hora_fin')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="ip-form-actions">
                            <a href="{{ route('cursos.index') }}" class="btn ip-btn-outline">Cancelar</a>
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
