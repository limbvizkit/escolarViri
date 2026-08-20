@extends('layouts.app')

@section('title', $escuela->nombre)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Detalle de la escuela</p>
        <a href="{{ route('escuelas.edit', $escuela) }}" class="btn ip-btn">
            <i class="bi bi-pencil-square me-1"></i>Editar
        </a>
    </div>

    <div class="ip-card mb-4">
        <div class="ip-card-header">
            <h5 class="ip-card-title">{{ $escuela->nombre }}</h5>
            <span class="badge ip-badge-{{ $escuela->estatus_badge }}">
                {{ $escuela->estatus_es_activo ? 'Activa' : 'Inactiva' }}
            </span>
        </div>
        <div class="ip-card-body">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="ip-detail-label">Clave</div>
                    <div class="ip-detail-value">{{ $escuela->clave }}</div>
                </div>
                <div class="col-md-3">
                    <div class="ip-detail-label">Teléfono</div>
                    <div class="ip-detail-value">{{ $escuela->telefono ?? '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="ip-detail-label">Correo</div>
                    <div class="ip-detail-value">{{ $escuela->email ?? '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="ip-detail-label">Sucursales</div>
                    <div class="ip-detail-value">{{ $escuela->sucursales->count() }}</div>
                </div>
                <div class="col-12">
                    <div class="ip-detail-label">Dirección</div>
                    <div class="ip-detail-value">{{ $escuela->direccion ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="ip-card">
        <div class="ip-card-header">
            <h5 class="ip-card-title">Sucursales de {{ $escuela->nombre }}</h5>
            <a href="{{ route('sucursales.create') }}" class="btn ip-btn btn-sm">Nueva sucursal</a>
        </div>
        <div class="table-responsive">
            <table class="table ip-table mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Empleados</th>
                        <th>Estatus</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($escuela->sucursales as $sucursal)
                        <tr>
                            <td class="fw-semibold">{{ $sucursal->nombre }}</td>
                            <td class="ip-muted">{{ $sucursal->direccion ?? '—' }}</td>
                            <td>{{ $sucursal->empleados_count }}</td>
                            <td>
                                <span class="badge ip-badge-{{ $sucursal->estatus_badge }}">
                                    {{ $sucursal->estatus_es_activo ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center ip-muted py-4">Sin sucursales registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection