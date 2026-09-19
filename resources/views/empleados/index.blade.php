@extends('layouts.app')

@section('title', 'Empleados')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Gestión de empleados por sucursal</p>
        <div class="d-flex gap-2">
            @php
                $exportQuery = array_filter(request()->only(['q', 'estatus', 'sucursal_id', 'sort', 'direction']), fn ($v) => $v !== null && $v !== '');
            @endphp
            <a href="{{ route('empleados.export.pdf', $exportQuery) }}" class="btn ip-btn-danger btn-sm">
                <i class="bi bi-file-earmark-pdf me-1"></i>PDF
            </a>
            <a href="{{ route('empleados.export.excel', $exportQuery) }}" class="btn ip-btn-success btn-sm">
                <i class="bi bi-file-earmark-excel me-1"></i>Excel
            </a>
            <a href="{{ route('empleados.create') }}" class="btn ip-btn">
                <i class="bi bi-plus-lg me-1"></i>Nuevo empleado
            </a>
        </div>
    </div>

    @include('partials.table-filters', [
        'filters' => $filtros,
        'placeholder' => 'Buscar por nombre, apellidos, correo, puesto, teléfono, CURP...',
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
                        <x-sortable field="puesto" label="Área / Puesto" :current="request('sort')" :direction="request('direction')" />
                        <th>Sucursal</th>
                        <x-sortable field="horario" label="Horario" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="fecha_nacimiento" label="Fecha de nac." :current="request('sort')" :direction="request('direction')" />
                        <th>Números de emergencias</th>
                        <x-sortable field="tipo_sangre" label="Tipo sangre" :current="request('sort')" :direction="request('direction')" />
                        <th>Enfermedad</th>
                        <th>Alergias</th>
                        <th>Medicamento</th>
                        <th>Dirección</th>
                        <th>Teléfono personal</th>
                        <x-sortable field="curp" label="CURP" :current="request('sort')" :direction="request('direction')" />
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($empleados as $empleado)
                        <tr>
                            <td class="fw-semibold">{{ $empleado->nombre_completo }}</td>
                            <td>
                                <span class="ip-cell-compact-sm d-block" title="{{ $empleado->puesto }}">
                                    {{ $empleado->puesto ?? '—' }}
                                </span>
                            </td>
                            <td>{{ $empleado->sucursal->nombre ?? '—' }}</td>
                            <td>
                                <span class="ip-cell-compact-sm d-block" title="{{ $empleado->horario }}">
                                    {{ $empleado->horario ?? '—' }}
                                </span>
                            </td>
                            <td>
                                {{ $empleado->fecha_nacimiento ? $empleado->fecha_nacimiento->format('d/m/Y') : '—' }}
                            </td>
                            <td>
                                <span class="ip-cell-compact-lg d-block" title="{{ $empleado->numeros_emergencia }}">
                                    {{ $empleado->numeros_emergencia ?? '—' }}
                                </span>
                            </td>
                            <td>{{ $empleado->tipo_sangre ?? '—' }}</td>
                            <td>
                                <span class="ip-cell-compact d-block" title="{{ $empleado->enfermedad }}">
                                    {{ $empleado->enfermedad ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <span class="ip-cell-compact d-block" title="{{ $empleado->alergias }}">
                                    {{ $empleado->alergias ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <span class="ip-cell-compact d-block" title="{{ $empleado->medicamento }}">
                                    {{ $empleado->medicamento ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <span class="ip-cell-compact-lg d-block" title="{{ $empleado->direccion }}">
                                    {{ $empleado->direccion ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <span class="ip-cell-compact-sm d-block" title="{{ $empleado->telefono_personal }}">
                                    {{ $empleado->telefono_personal ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <span class="ip-cell-compact-sm d-block" title="{{ $empleado->curp }}">
                                    {{ $empleado->curp ?? '—' }}
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
                            <td colspan="14" class="text-center ip-muted py-4">
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
