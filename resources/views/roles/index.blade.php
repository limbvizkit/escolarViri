@extends('layouts.app')

@section('title', 'Roles')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Gestión de roles de acceso</p>
        <a href="{{ route('roles.create') }}" class="btn ip-btn">
            <i class="bi bi-plus-lg me-1"></i>Nuevo rol
        </a>
    </div>

    @include('partials.table-filters', [
        'filters' => $filtros,
        'placeholder' => 'Buscar por nombre o slug...',
    ])

    <div class="ip-card">
        <div class="ip-card-header">
            <span class="ip-table-summary">Mostrando {{ $roles->currentPage() }} de {{ $roles->lastPage() }}</span>
            <h5 class="ip-card-title">Listado de roles</h5>
        </div>

        <div class="table-responsive">
            <table class="table ip-table mb-0">
                <thead>
                    <tr>
                        <x-sortable field="id" label="#" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="nombre" label="Nombre" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="slug" label="Slug" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="usuarios_count" label="Usuarios" :current="request('sort')" :direction="request('direction')" />
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $rol)
                        <tr>
                            <td>{{ $rol->id }}</td>
                            <td class="fw-semibold">{{ $rol->nombre }}</td>
                            <td><span class="badge text-bg-light border">{{ $rol->slug }}</span></td>
                            <td><span class="badge ip-badge-active">{{ $rol->usuarios_count }}</span></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('roles.show', $rol) }}" class="ip-action" title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('roles.edit', $rol) }}" class="ip-action" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('roles.destroy', $rol) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar este rol?')">
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
                            <td colspan="5" class="text-center ip-muted py-4">
                                No hay roles registrados.
                                <a href="{{ route('roles.create') }}" class="d-block mt-2">Crear el primero</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ip-card-body d-flex justify-content-center">
            {{ $roles->links() }}
        </div>
    </div>
@endsection