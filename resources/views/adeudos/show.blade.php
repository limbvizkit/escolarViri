@extends('layouts.app')

@section('title', 'Adeudo · ' . $adeudo->concepto)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">{{ $adeudo->alumno->nombre_completo }}</p>
        <a href="{{ route('adeudos.index') }}" class="btn ip-btn-outline">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="ip-card mb-4">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Detalle del adeudo</h5>
                </div>
                <div class="ip-card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="ip-detail-label">ALUMNO</div>
                            <div class="ip-detail-value">{{ $adeudo->alumno->nombre_completo }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="ip-detail-label">CONCEPTO</div>
                            <div class="ip-detail-value">{{ $adeudo->concepto }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="ip-detail-label">ANOTACIONES</div>
                            <div class="ip-detail-value">{{ $adeudo->anotaciones ?? '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="ip-detail-label">MONTO</div>
                            <div class="ip-detail-value">{{ '$' . number_format((float) $adeudo->monto, 2) }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="ip-detail-label">ABONADO</div>
                            <div class="ip-detail-value">{{ '$' . number_format((float) $adeudo->monto_pagado, 2) }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="ip-detail-label">PENDIENTE</div>
                            <div class="ip-detail-value">
                                @if ($adeudo->pendiente > 0)
                                    <span class="fw-semibold text-danger">{{ $adeudo->pendienteFormateado }}</span>
                                @else
                                    <span class="fw-semibold text-success">{{ $adeudo->pendienteFormateado }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="ip-detail-label">ESTATUS</div>
                            <div class="ip-detail-value">
                                @php
                                    $badge = match ($adeudo->estatus) {
                                        \App\Models\Adeudo::ESTATUS_PAGADO => 'badge ip-badge-active',
                                        \App\Models\Adeudo::ESTATUS_PARCIAL => 'badge text-bg-info',
                                        default => 'badge text-bg-warning',
                                    };
                                @endphp
                                <span class="{{ $badge }}">{{ ucfirst($adeudo->estatus) }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ip-detail-label">FECHA DE CREACIÓN</div>
                            <div class="ip-detail-value">{{ $adeudo->created_at?->format('d/m/Y') ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Histórico de abonos</h5>
                </div>
                <div class="table-responsive">
                    <table class="table ip-table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th class="text-end">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($adeudo->abonos as $abono)
                                <tr>
                                    <td>{{ $abono->id }}</td>
                                    <td>{{ $abono->fecha?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="text-end">{{ '$' . number_format((float) $abono->monto, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center ip-muted py-4">Sin abonos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            @if ($adeudo->estatus === \App\Models\Adeudo::ESTATUS_PAGADO)
                <div class="ip-card">
                    <div class="ip-card-header">
                        <h5 class="ip-card-title">Registrar abono</h5>
                    </div>
                    <div class="ip-card-body">
                        <div class="alert alert-success ip-alert mb-0" role="alert">
                            <i class="bi bi-check-circle-fill me-1"></i>Este adeudo ya está liquidado.
                        </div>
                    </div>
                </div>
            @else
                <div class="ip-card">
                    <div class="ip-card-header">
                        <h5 class="ip-card-title">Registrar abono</h5>
                    </div>
                    <div class="ip-card-body">
                        <form action="{{ route('adeudos.abonar', $adeudo) }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="monto" class="form-label">Monto del abono <span class="ip-required">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0.01" id="monto" name="monto"
                                           class="form-control @error('monto') is-invalid @enderror"
                                           value="{{ old('monto') }}" required>
                                </div>
                                @error('monto')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                <div class="form-text">Pendiente actual: {{ $adeudo->pendienteFormateado }}</div>
                            </div>

                            <div class="mb-3">
                                <label for="fecha" class="form-label">Fecha</label>
                                <input type="date" id="fecha" name="fecha"
                                       class="form-control @error('fecha') is-invalid @enderror"
                                       value="{{ old('fecha', now()->format('Y-m-d')) }}">
                                @error('fecha')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="ip-form-actions">
                                <button type="submit" class="btn ip-btn-success">
                                    <i class="bi bi-plus-lg me-1"></i>Registrar abono
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection