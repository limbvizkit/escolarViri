@extends('layouts.portal')

@section('title', 'Detalle del pago')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="ip-card">
                <div class="ip-card-header d-flex justify-content-between align-items-center">
                    <h5 class="ip-card-title mb-0">Detalle del pago</h5>
                    <span class="badge {{ $payment->statusBadgeClass() }}">
                        {{ $payment->statusLabel() }}
                    </span>
                </div>
                <div class="ip-card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Concepto</dt>
                        <dd class="col-sm-8">{{ $payment->concepto }}</dd>

                        <dt class="col-sm-4">Monto</dt>
                        <dd class="col-sm-8">${{ number_format($payment->monto, 2) }} {{ $payment->moneda }}</dd>

                        <dt class="col-sm-4">Fecha</dt>
                        <dd class="col-sm-8">{{ $payment->created_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-4">Número de orden</dt>
                        <dd class="col-sm-8">{{ $payment->order_id }}</dd>

                        @if ($payment->openpay_charge_id)
                            <dt class="col-sm-4">Referencia OpenPay</dt>
                            <dd class="col-sm-8">{{ $payment->openpay_charge_id }}</dd>
                        @endif

                        @if ($payment->authorization)
                            <dt class="col-sm-4">Autorización</dt>
                            <dd class="col-sm-8">{{ $payment->authorization }}</dd>
                        @endif

                        @if ($payment->card_brand || $payment->card_last4)
                            <dt class="col-sm-4">Tarjeta</dt>
                            <dd class="col-sm-8">
                                @if ($payment->card_brand)
                                    {{ ucfirst($payment->card_brand) }}
                                @endif
                                @if ($payment->card_last4)
                                    terminada en {{ $payment->card_last4 }}
                                @endif
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="ip-form-actions mt-3">
                <a href="{{ route('portal.payments.index') }}" class="btn ip-btn-outline">
                    <i class="bi bi-arrow-left me-1"></i>Volver al historial
                </a>
            </div>
        </div>
    </div>
@endsection
