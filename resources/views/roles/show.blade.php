@extends('layouts.app')

@section('title', $rol->nombre)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Detalle del rol</p>
        <a href="{{ route('roles.edit', $rol) }}" class="btn ip-btn">
            <i class="bi bi-pencil-square me-1"></i>Editar
        </a>
    </div>

    <div class="ip-card">
        <div class="ip-card-header">
            <h5 class="ip-card-title">{{ $rol->nombre }}
                <span class="ip-muted fw-normal">· {{ $rol->slug }}</span>
            </h5>
            <span class="badge ip-badge-active">{{ $rol->usuarios->count() }} usuarios</span>
        </div>

        <div class="table-responsive">
            <table class="table ip-table mb-0">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Empleado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rol->usuarios as $usuario)
                        <tr>
                            <td class="fw-semibold">{{ $usuario->name }}</td>
                            <td class="ip-muted">{{ $usuario->email }}</td>
                            <td>{{ $usuario->empleado->nombre_completo ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center ip-muted py-4">Sin usuarios asignados a este rol.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection