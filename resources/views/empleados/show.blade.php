@extends('layouts.app')

@section('title', $empleado->nombre_completo)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Detalle del empleado</p>
        <div class="d-flex gap-2">
            <a href="{{ route('empleados.edit', $empleado) }}" class="btn ip-btn-outline">Editar</a>
            @if (!$empleado->usuario)
                <a href="{{ route('usuarios.create', ['empleado_id' => $empleado->id]) }}" class="btn ip-btn">
                    <i class="bi bi-person-plus me-1"></i>Crear usuario
                </a>
            @endif
        </div>
    </div>

    <div class="ip-card">
        <div class="ip-card-header">
            <h5 class="ip-card-title">{{ $empleado->nombre_completo }}</h5>
            <span class="badge ip-badge-{{ $empleado->estatus_badge }}">
                {{ $empleado->estatus_es_activo ? 'Activo' : 'Inactivo' }}
            </span>
        </div>
        <div class="ip-card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="ip-detail-label">Sucursal</div>
                    <div class="ip-detail-value">{{ $empleado->sucursal->nombre ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Puesto</div>
                    <div class="ip-detail-value">{{ $empleado->puesto ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Teléfono</div>
                    <div class="ip-detail-value">{{ $empleado->telefono ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="ip-detail-label">Correo</div>
                    <div class="ip-detail-value">{{ $empleado->email ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="ip-detail-label">Usuario asociado</div>
                    <div class="ip-detail-value">
                        @if ($empleado->usuario)
                            {{ $empleado->usuario->name }} ({{ $empleado->usuario->email }})
                        @else
                            <span class="ip-muted">Sin usuario asignado</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection