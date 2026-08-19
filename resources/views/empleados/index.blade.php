@extends('layouts.app')

@section('title', 'Empleados')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Gestión de empleados por sucursal</p>
        <a href="{{ route('empleados.create') }}" class="btn ip-btn">
            <i class="bi bi-plus-lg me-1"></i>Nuevo empleado
        </a>
    </div>

    @include('partials.table-filters', [
        'filters' => $filtros,
        'placeholder' => 'Buscar por nombre, apellidos, correo, puesto...',
    ])

    <div class="ip-card">
        <div class="ip-card-header">
            <span class="ip-table-summary">Mostrando {{ $empleados->currentPage() }} de {{ $empleados->lastPage() }}</span>
            <h5 class="ip-card-title">Listado de empleados</h5>
        </div>

        <div class="table-responsive">
            <table class="table ip-table mb-0">
                <thead>
                    <tr>
                        <x-sortable field="apellido_paterno" label="Nombre completo" :current="request('sort')" :direction="request('direction')" />
                        <th>Sucursal</th>
                        <x-sortable field="puesto" label="Puesto" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="email" label="Correo" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="estatus" label="Estatus" :current="request('sort')" :direction="request('direction')" />
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($empleados as $empleado)
                        <tr>
                            <td class="fw-semibold">{{ $empleado->nombre_completo }}</td>
                            <td>{{ $empleado->sucursal->nombre ?? '—' }}</td>
                            <td>{{ $empleado->puesto ?? '—' }}</td>
                            <td class="ip-muted">{{ $empleado->email ?? '—' }}</td>
                            <td>
                                <span class="badge ip-badge-{{ $empleado->estatus ? 'active' : 'inactive' }}">
                                    {{ $empleado->estatus ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('empleados.show', $empleado) }}" class="ip-action" title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('empleados.edit', $empleado) }}" class="ip-action" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('empleados.destroy', $empleado) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar este empleado?')">
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
                                No hay empleados registrados.
                                <a href="{{ route('empleados.create') }}" class="d-block mt-2">Crear el primero</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ip-card-body d-flex justify-content-center">
            {{ $empleados->links() }}
        </div>
    </div>
@endsection