@extends('layouts.app')

@section('title', 'Adeudos')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Adeudos y abonos por alumno</p>
        <a href="{{ route('adeudos.create') }}" class="btn ip-btn">
            <i class="bi bi-plus-lg me-1"></i>Nuevo adeudo
        </a>
    </div>

    @include('partials.table-filters', [
        'filters' => $filtros,
        'placeholder' => 'Buscar por alumno o concepto...',
    ])

    <div class="ip-card">
        <div class="ip-card-header">
            <span class="ip-table-summary">Mostrando {{ $adeudos->currentPage() }} de {{ $adeudos->lastPage() }}</span>
            <h5 class="ip-card-title">Listado de adeudos</h5>
        </div>

        <div class="table-responsive">
            <table class="table ip-table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Alumno</th>
                        <th>Concepto</th>
                        <th>Anotaciones</th>
                        <th class="text-end">Monto</th>
                        <th class="text-end">Abonado</th>
                        <th class="text-end">Pendiente</th>
                        <th>Estatus</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($adeudos as $adeudo)
                        <tr>
                            <td>{{ $adeudo->id }}</td>
                            <td>
                                <a href="{{ route('alumnos.show', $adeudo->alumno) }}" class="fw-semibold ip-link">
                                    {{ $adeudo->alumno->nombre_completo }}
                                </a>
                                <span class="badge ms-1" style="background:#eaf1ff;color:var(--ip-primary);font-weight:600;">
                                    {{ $adeudo->alumno->gradoEscolar->nombre ?? '—' }}
                                </span>
                            </td>
                            <td>{{ $adeudo->concepto }}</td>
                            <td class="ip-muted">{{ Str::limit($adeudo->anotaciones ?? '', 60) ?: '—' }}</td>
                            <td class="text-end">{{ '$' . number_format((float) $adeudo->monto, 2) }}</td>
                            <td class="text-end">{{ '$' . number_format((float) $adeudo->monto_pagado, 2) }}</td>
                            <td class="text-end">
                                @if ($adeudo->pendiente > 0)
                                    <span class="fw-semibold text-danger">{{ $adeudo->pendienteFormateado }}</span>
                                @else
                                    <span class="fw-semibold text-success">{{ $adeudo->pendienteFormateado }}</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badge = match ($adeudo->estatus) {
                                        \App\Models\Adeudo::ESTATUS_PAGADO => 'badge ip-badge-active',
                                        \App\Models\Adeudo::ESTATUS_PARCIAL => 'badge text-bg-info',
                                        default => 'badge text-bg-warning',
                                    };
                                @endphp
                                <span class="{{ $badge }}">{{ ucfirst($adeudo->estatus) }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('adeudos.show', $adeudo) }}" class="ip-action" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center ip-muted py-4">
                                No hay adeudos registrados.
                                <a href="{{ route('adeudos.create') }}" class="d-block mt-2">Registrar el primero</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ip-card-body d-flex justify-content-center">
            {{ $adeudos->links() }}
        </div>
    </div>
@endsection