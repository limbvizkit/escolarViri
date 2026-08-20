@extends('layouts.app')

@section('title', 'Grados Escolares')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Catálogo de grados escolares</p>
        <a href="{{ route('grados-escolares.create') }}" class="btn ip-btn">
            <i class="bi bi-plus-lg me-1"></i>Nuevo grado escolar
        </a>
    </div>

    @include('partials.table-filters', [
        'filters' => $filtros,
        'placeholder' => 'Buscar por nombre del grado escolar...',
    ])

    <div class="ip-card">
        <div class="ip-card-header">
            <span class="ip-table-summary">Mostrando {{ $gradosEscolares->currentPage() }} de {{ $gradosEscolares->lastPage() }}</span>
            <h5 class="ip-card-title">Listado de grados escolares</h5>
        </div>

        <div class="table-responsive">
            <table class="table ip-table mb-0">
                <thead>
                    <tr>
                        <x-sortable field="id" label="#" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="nombre" label="Grado Escolar" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="alumnos_count" label="Alumnos" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="estatus_id" label="Estatus" :current="request('sort')" :direction="request('direction')" />
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($gradosEscolares as $gradoEscolar)
                        <tr>
                            <td>{{ $gradoEscolar->id }}</td>
                            <td class="fw-semibold">{{ $gradoEscolar->nombre }}</td>
                            <td><span class="badge ip-badge-active">{{ $gradoEscolar->alumnos_count }}</span></td>
                            <td>
                                <span class="badge ip-badge-{{ $gradoEscolar->estatus_badge }}">
                                    {{ $gradoEscolar->estatus_es_activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('grados-escolares.show', $gradoEscolar) }}" class="ip-action" title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('grados-escolares.edit', $gradoEscolar) }}" class="ip-action" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('grados-escolares.destroy', $gradoEscolar) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar este grado escolar?')">
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
                                No hay grados escolares registrados.
                                <a href="{{ route('grados-escolares.create') }}" class="d-block mt-2">Crear el primero</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ip-card-body d-flex justify-content-center">
            {{ $gradosEscolares->links() }}
        </div>
    </div>
@endsection