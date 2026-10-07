@extends('layouts.app')

@section('title', 'Pagos lunch')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Pagos de lunch por alumno</p>
        <div class="d-flex gap-2">
            @php
                $exportQuery = array_filter(request()->only(['q', 'mes', 'sort', 'direction']), fn ($v) => $v !== null && $v !== '');
            @endphp
            <a href="{{ route('pagos-lunch.export.pdf', $exportQuery) }}" class="btn ip-btn-danger btn-sm">
                <i class="bi bi-file-earmark-pdf me-1"></i>PDF
            </a>
            <a href="{{ route('pagos-lunch.export.excel', $exportQuery) }}" class="btn ip-btn-success btn-sm">
                <i class="bi bi-file-earmark-excel me-1"></i>Excel
            </a>
            <a href="{{ route('pagos-lunch.create') }}" class="btn ip-btn">
                <i class="bi bi-plus-lg me-1"></i>Nuevo pago de lunch
            </a>
        </div>
    </div>

    @include('partials.table-filters', [
        'filters' => $filtros,
        'placeholder' => 'Buscar por alumno...',
    ])

    <div class="ip-card">
        <div class="ip-card-header">
            <span class="ip-table-summary">Mostrando {{ $pagos->currentPage() }} de {{ $pagos->lastPage() }}</span>
            <h5 class="ip-card-title">Listado de pagos de lunch</h5>
        </div>

        <div class="table-responsive">
            <table class="table ip-table mb-0" id="pagos-lunch-table">
                <thead>
                    <tr>
                        <x-sortable field="id" label="#" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="alumno_id" label="Alumno" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="mes" label="Mes" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="monto" label="Monto pagado" :current="request('sort')" :direction="request('direction')" />
                        <th>Observaciones</th>
                        <x-sortable field="created_at" label="Registrado" :current="request('sort')" :direction="request('direction')" />
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pagos as $pago)
                        <tr>
                            <td>{{ $pago->id }}</td>
                            <td>
                                <a href="{{ route('alumnos.show', $pago->alumno) }}" class="fw-semibold ip-link">
                                    {{ $pago->alumno->nombre_completo }}
                                </a>
                                <span class="badge ms-1" style="background:#eaf1ff;color:var(--ip-primary);font-weight:600;">
                                    {{ $pago->alumno->gradoEscolar->nombre ?? '—' }}
                                </span>
                            </td>
                            <td>{{ $pago->mes_label }}</td>
                            <td class="text-end">{{ '$' . number_format((float) $pago->monto, 2) }}</td>
                            <td class="ip-muted">{{ Str::limit($pago->observaciones ?? '', 60) ?: '—' }}</td>
                            <td>{{ $pago->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('pagos-lunch.edit', $pago) }}" class="ip-action" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('pagos-lunch.destroy', $pago) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar este pago?')">
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
                                No hay pagos de lunch registrados.
                                <a href="{{ route('pagos-lunch.create') }}" class="d-block mt-2">Registrar el primero</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ip-card-body d-flex justify-content-center">
            {{ $pagos->links() }}
        </div>
    </div>
@endsection
