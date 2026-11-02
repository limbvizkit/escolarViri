@extends('layouts.app')

@section('title', $rol->nombre)

@section('content')
    @php
        $puedeEscribir = auth()->user()?->puedeEscribir('roles') ?? false;
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Detalle del rol</p>
        @if ($puedeEscribir)
            <a href="{{ route('roles.edit', $rol) }}" class="btn ip-btn">
                <i class="bi bi-pencil-square me-1"></i>Editar
            </a>
        @endif
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

    <div class="ip-card mt-3">
        <div class="ip-card-header">
            <h5 class="ip-card-title">Permisos efectivos</h5>
            @if ($rol->slug === 'super-admin')
                <span class="badge ip-badge-active">Acceso total</span>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table ip-table mb-0">
                <thead>
                    <tr>
                        <th>Módulo</th>
                        <th class="text-center">Ver</th>
                        <th class="text-center">Escribir</th>
                        <th class="text-center">Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($modulos as $grupo => $items)
                        <tr class="table-light">
                            <td colspan="4" class="fw-semibold">{{ $grupo }}</td>
                        </tr>
                        @foreach ($items as $modulo)
                            @php $permiso = $permisos[$modulo->id] ?? null; @endphp
                            <tr>
                                <td>{{ $modulo->nombre }}</td>
                                @foreach (['ver' => 'puede_ver', 'escribir' => 'puede_escribir', 'eliminar' => 'puede_eliminar'] as $etiqueta => $atributo)
                                    @php $concedido = $rol->slug === 'super-admin' || (bool) $permiso?->{$atributo}; @endphp
                                    <td class="text-center">
                                        <span class="badge {{ $concedido ? 'ip-badge-active' : 'text-bg-light border' }}">
                                            {{ $concedido ? 'Sí' : 'No' }}
                                        </span>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
