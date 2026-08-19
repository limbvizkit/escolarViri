@extends('layouts.app')

@section('title', 'Sucursales')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Gestión de sucursales por escuela</p>
        <a href="{{ route('sucursales.create') }}" class="btn ip-btn">
            <i class="bi bi-plus-lg me-1"></i>Nueva sucursal
        </a>
    </div>

    @include('partials.table-filters', [
        'filters' => $filtros,
        'placeholder' => 'Buscar por nombre, dirección, escuela...',
    ])

    <div class="ip-card">
        <div class="ip-card-header">
            <span class="ip-table-summary">Mostrando {{ $sucursales->currentPage() }} de {{ $sucursales->lastPage() }}</span>
            <h5 class="ip-card-title">Listado de sucursales</h5>
        </div>

        <div class="table-responsive">
            <table class="table ip-table mb-0">
                <thead>
                    <tr>
                        <x-sortable field="id" label="#" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="nombre" label="Nombre" :current="request('sort')" :direction="request('direction')" />
                        <th>Escuela</th>
                        <x-sortable field="direccion" label="Dirección" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="empleados_count" label="Empleados" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="estatus" label="Estatus" :current="request('sort')" :direction="request('direction')" />
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sucursales as $sucursal)
                        <tr>
                            <td>{{ $sucursal->id }}</td>
                            <td class="fw-semibold">{{ $sucursal->nombre }}</td>
                            <td>{{ $sucursal->escuela->nombre ?? '—' }}</td>
                            <td class="ip-muted">{{ $sucursal->direccion ?? '—' }}</td>
                            <td><span class="badge ip-badge-active">{{ $sucursal->empleados_count }}</span></td>
                            <td>
                                <span class="badge ip-badge-{{ $sucursal->estatus ? 'active' : 'inactive' }}">
                                    {{ $sucursal->estatus ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('sucursales.show', $sucursal) }}" class="ip-action" title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('sucursales.edit', $sucursal) }}" class="ip-action" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('sucursales.destroy', $sucursal) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar esta sucursal?')">
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
                            <td colspan="7" class="text-center ip-muted py-4">
                                No hay sucursales registradas.
                                <a href="{{ route('sucursales.create') }}" class="d-block mt-2">Crear la primera</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ip-card-body d-flex justify-content-center">
            {{ $sucursales->links() }}
        </div>
    </div>
@endsection