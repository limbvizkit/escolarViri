@extends('layouts.app')

@section('title', $usuario->name)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Detalle del usuario</p>
        <a href="{{ route('usuarios.edit', $usuario) }}" class="btn ip-btn">
            <i class="bi bi-pencil-square me-1"></i>Editar
        </a>
    </div>

    <div class="ip-card">
        <div class="ip-card-header">
            <h5 class="ip-card-title">
                <span class="ip-avatar me-2">{{ strtoupper(substr($usuario->name, 0, 1)) }}</span>
                {{ $usuario->name }}
            </h5>
            @if ($usuario->rol)
                <span class="badge" style="background:#f1eaff;color:var(--ip-accent);font-weight:600;">
                    {{ $usuario->rol->nombre }}
                </span>
            @endif
        </div>
        <div class="ip-card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="ip-detail-label">Correo electrónico</div>
                    <div class="ip-detail-value">{{ $usuario->email }}</div>
                </div>
                <div class="col-md-6">
                    <div class="ip-detail-label">Rol</div>
                    <div class="ip-detail-value">{{ $usuario->rol->nombre ?? 'Sin rol' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="ip-detail-label">Empleado</div>
                    <div class="ip-detail-value">{{ $usuario->empleado->nombre_completo ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="ip-detail-label">Sucursal del empleado</div>
                    <div class="ip-detail-value">{{ $usuario->empleado->sucursal->nombre ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection