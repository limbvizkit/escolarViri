@extends('layouts.app')

@section('title', 'Pago de ' . $pago->alumno->nombre_completo)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Detalle del pago mensual</p>
        <a href="{{ route('pagos.edit', $pago) }}" class="btn ip-btn">
            <i class="bi bi-pencil-square me-1"></i>Editar
        </a>
    </div>

    <div class="ip-card">
        <div class="ip-card-header">
            <h5 class="ip-card-title">
                {{ $pago->alumno->nombre_completo }}
            </h5>
            <span class="badge" style="background:#eaf1ff;color:var(--ip-primary);font-weight:600;">{{ $pago->mes_label }}</span>
        </div>
        <div class="ip-card-body">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="ip-detail-label">FECHA</div>
                    <div class="ip-detail-value">{{ $pago->fecha?->format('d/m/Y') ?? '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="ip-detail-label">ENTRADA 8AM</div>
                    <div class="ip-detail-value">{{ $pago->entrada_8am !== null ? '$' . number_format((float) $pago->entrada_8am, 2) : '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="ip-detail-label">PRONTO PAGO</div>
                    <div class="ip-detail-value">{{ $pago->pronto_pago !== null ? '$' . number_format((float) $pago->pronto_pago, 2) : '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="ip-detail-label">PAGO NORMAL</div>
                    <div class="ip-detail-value">{{ $pago->pago_normal !== null ? '$' . number_format((float) $pago->pago_normal, 2) : '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="ip-detail-label">FORMA DE PAGO</div>
                    <div class="ip-detail-value">{{ $pago->formaPago->nombre ?? '—' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="ip-detail-label">TALLERES</div>
                    <div class="ip-detail-value">{{ $pago->talleres !== null ? '$' . number_format((float) $pago->talleres, 2) : '—' }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection