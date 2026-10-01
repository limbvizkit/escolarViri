@extends('layouts.app')

@section('title', 'Detalle del pago en línea')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Información del pago procesado por OpenPay</p>
        <a href="{{ route('online-payments.index') }}" class="btn ip-btn-outline btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Volver al listado
        </a>
    </div>

    <div class="ip-card">
        <div class="ip-card-header d-flex justify-content-between align-items-center">
            <h5 class="ip-card-title mb-0">Pago #{{ $payment->id }}</h5>
            <span class="badge {{ $payment->statusBadgeClass() }}">
                {{ $payment->statusLabel() }}
            </span>
        </div>
        <div class="ip-card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="ip-detail-label">USUARIO DEL PORTAL</div>
                    <div class="ip-detail-value">{{ $payment->portalUser->name ?? '—' }}</div>
                    <div class="ip-muted small">{{ $payment->portalUser->email ?? '' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">CONCEPTO</div>
                    <div class="ip-detail-value">{{ $payment->concepto }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">MONTO</div>
                    <div class="ip-detail-value">${{ number_format((float) $payment->monto, 2) }} {{ $payment->moneda }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">NÚMERO DE ORDEN</div>
                    <div class="ip-detail-value">{{ $payment->order_id }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">OPENPAY CHARGE ID</div>
                    <div class="ip-detail-value">{{ $payment->openpay_charge_id ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">AUTORIZACIÓN</div>
                    <div class="ip-detail-value">{{ $payment->authorization ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">TARJETA</div>
                    <div class="ip-detail-value">
                        @if ($payment->card_brand || $payment->card_last4)
                            @if ($payment->card_brand)
                                {{ ucfirst($payment->card_brand) }}
                            @endif
                            @if ($payment->card_last4)
                                terminada en {{ $payment->card_last4 }}
                            @endif
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">FECHA</div>
                    <div class="ip-detail-value">{{ $payment->created_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
