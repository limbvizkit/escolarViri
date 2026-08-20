@extends('layouts.app')

@section('title', 'Escuelas')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Gestión de escuelas del sistema</p>
        <a href="{{ route('escuelas.create') }}" class="btn ip-btn">
            <i class="bi bi-plus-lg me-1"></i>Nueva escuela
        </a>
    </div>

    @include('partials.table-filters', [
        'filters' => $filtros,
        'placeholder' => 'Buscar por nombre, clave, dirección, correo...',
    ])

    <div class="ip-card">
        <div class="ip-card-header">
            <span class="ip-table-summary">Mostrando {{ $escuelas->currentPage() }} de {{ $escuelas->lastPage() }}</span>
            <h5 class="ip-card-title">Listado de escuelas</h5>
        </div>

        <div class="table-responsive">
            <table class="table ip-table mb-0">
                <thead>
                    <tr>
                        <x-sortable field="id" label="#" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="clave" label="Clave" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="nombre" label="Nombre" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="direccion" label="Dirección" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="sucursales_count" label="Sucursales" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="estatus_id" label="Estatus" :current="request('sort')" :direction="request('direction')" />
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($escuelas as $escuela)
                        <tr>
                            <td>{{ $escuela->id }}</td>
                            <td><span class="badge text-bg-light border">{{ $escuela->clave }}</span></td>
                            <td class="fw-semibold">{{ $escuela->nombre }}</td>
                            <td class="ip-muted">{{ $escuela->direccion ?? '—' }}</td>
                            <td>
                                <span class="badge ip-badge-active">{{ $escuela->sucursales_count }}</span>
                            </td>
                            <td>
                                <span class="badge ip-badge-{{ $escuela->estatus_badge }}">
                                    {{ $escuela->estatus_es_activo ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('escuelas.show', $escuela) }}" class="ip-action" title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('escuelas.edit', $escuela) }}" class="ip-action" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('escuelas.destroy', $escuela) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar esta escuela?')">
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
                                No hay escuelas registradas.
                                <a href="{{ route('escuelas.create') }}" class="d-block mt-2">Crear la primera</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ip-card-body d-flex justify-content-center">
            {{ $escuelas->links() }}
        </div>
    </div>
@endsection