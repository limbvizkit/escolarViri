@extends('layouts.portal')

@section('title', 'Inicio')

@section('content')
    <div class="row g-4">
        <div class="col-12">
            <div class="ip-card">
                <div class="ip-card-body">
                    <h5 class="ip-card-title">Bienvenido, {{ Auth::guard('portal')->user()->name }}</h5>
                    <p class="ip-muted mb-0">
                        Realiza pagos en línea de forma segura y consulta tu historial.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="ip-card h-100">
                <div class="ip-card-body d-flex flex-column">
                    <div class="mb-3">
                        <i class="bi bi-credit-card fs-1 ip-primary"></i>
                    </div>
                    <h5 class="ip-card-title">Realizar un pago</h5>
                    <p class="ip-muted flex-grow-1">
                        Captura el importe y el concepto para pagar con tarjeta de crédito o débito.
                    </p>
                    <a href="{{ route('portal.payments.create') }}" class="btn ip-btn">
                        <i class="bi bi-credit-card me-1"></i>Pagar ahora
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="ip-card h-100">
                <div class="ip-card-body d-flex flex-column">
                    <div class="mb-3">
                        <i class="bi bi-receipt fs-1 ip-primary"></i>
                    </div>
                    <h5 class="ip-card-title">Mis pagos</h5>
                    <p class="ip-muted flex-grow-1">
                        Consulta el historial de tus pagos realizados desde el portal.
                    </p>
                    <a href="{{ route('portal.payments.index') }}" class="btn ip-btn-outline">
                        <i class="bi bi-receipt me-1"></i>Ver historial
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
