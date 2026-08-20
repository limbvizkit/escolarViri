@extends('layouts.app')

@section('title', $sucursal->nombre)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Detalle de la sucursal</p>
        <a href="{{ route('sucursales.edit', $sucursal) }}" class="btn ip-btn">
            <i class="bi bi-pencil-square me-1"></i>Editar
        </a>
    </div>

    <div class="ip-card mb-4">
        <div class="ip-card-header">
            <h5 class="ip-card-title">{{ $sucursal->nombre }}
                <span class="ip-muted fw-normal">· {{ $sucursal->escuela->nombre ?? 'Sin escuela' }}</span>
            </h5>
            <span class="badge ip-badge-{{ $sucursal->estatus_badge }}">
                {{ $sucursal->estatus_es_activo ? 'Activa' : 'Inactiva' }}
            </span>
        </div>
        <div class="ip-card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="ip-detail-label">Teléfono</div>
                    <div class="ip-detail-value">{{ $sucursal->telefono ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Correo</div>
                    <div class="ip-detail-value">{{ $sucursal->email ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Empleados</div>
                    <div class="ip-detail-value">{{ $sucursal->empleados->count() }}</div>
                </div>
                <div class="col-12">
                    <div class="ip-detail-label">Dirección</div>
                    <div class="ip-detail-value">{{ $sucursal->direccion ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="ip-card">
        <div class="ip-card-header">
            <h5 class="ip-card-title">Empleados de {{ $sucursal->nombre }}</h5>
            <a href="{{ route('empleados.create') }}" class="btn ip-btn btn-sm">Nuevo empleado</a>
        </div>
        <div class="table-responsive">
            <table class="table ip-table mb-0">
                <thead>
                    <tr>
                        <th>Nombre completo</th>
                        <th>Puesto</th>
                        <th>Correo</th>
                        <th>Estatus</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sucursal->empleados as $empleado)
                        <tr>
                            <td class="fw-semibold">{{ $empleado->nombre_completo }}</td>
                            <td>{{ $empleado->puesto ?? '—' }}</td>
                            <td class="ip-muted">{{ $empleado->email ?? '—' }}</td>
                            <td>
                                <span class="badge ip-badge-{{ $empleado->estatus_badge }}">
                                    {{ $empleado->estatus_es_activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center ip-muted py-4">Sin empleados registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection