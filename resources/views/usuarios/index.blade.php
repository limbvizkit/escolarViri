@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Gestión de usuarios del sistema</p>
        <a href="{{ route('usuarios.create') }}" class="btn ip-btn">
            <i class="bi bi-plus-lg me-1"></i>Nuevo usuario
        </a>
    </div>

    @include('partials.table-filters', [
        'filters' => $filtros,
        'placeholder' => 'Buscar por nombre o correo...',
    ])

    <div class="ip-card">
        <div class="ip-card-header">
            <span class="ip-table-summary">Mostrando {{ $usuarios->currentPage() }} de {{ $usuarios->lastPage() }}</span>
            <h5 class="ip-card-title">Listado de usuarios</h5>
        </div>

        <div class="table-responsive">
            <table class="table ip-table mb-0">
                <thead>
                    <tr>
                        <x-sortable field="id" label="#" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="name" label="Nombre" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="email" label="Correo" :current="request('sort')" :direction="request('direction')" />
                        <th>Empleado</th>
                        <th>Rol</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($usuarios as $usuario)
                        <tr>
                            <td>{{ $usuario->id }}</td>
                            <td class="fw-semibold">{{ $usuario->name }}</td>
                            <td class="ip-muted">{{ $usuario->email }}</td>
                            <td>{{ $usuario->empleado->nombre_completo ?? '—' }}</td>
                            <td>
                                @if ($usuario->rol)
                                    <span class="badge" style="background:#f1eaff;color:var(--ip-accent);font-weight:600;">
                                        {{ $usuario->rol->nombre }}
                                    </span>
                                @else
                                    <span class="ip-muted">Sin rol</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('usuarios.show', $usuario) }}" class="ip-action" title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('usuarios.edit', $usuario) }}" class="ip-action" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('usuarios.destroy', $usuario) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ip-action ip-action-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center ip-muted py-4">
                                No hay usuarios registrados.
                                <a href="{{ route('usuarios.create') }}" class="d-block mt-2">Crear el primero</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ip-card-body d-flex justify-content-center">
            {{ $usuarios->links() }}
        </div>
    </div>
@endsection