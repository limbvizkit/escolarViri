@extends('layouts.portal')

@section('title', 'Mis pagos')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="ip-heading mb-0">Mis pagos</h5>
                <a href="{{ route('portal.payments.create') }}" class="btn ip-btn">
                    <i class="bi bi-credit-card me-1"></i>Realizar pago
                </a>
            </div>

            <div class="ip-card">
                <div class="table-responsive">
                    <table class="table ip-table mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Concepto</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payments as $payment)
                                <tr>
                                    <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $payment->concepto }}</td>
                                    <td>${{ number_format($payment->monto, 2) }} {{ $payment->moneda }}</td>
                                    <td>
                                        <span class="badge {{ $payment->statusBadgeClass() }}">
                                            {{ $payment->statusLabel() }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('portal.payments.show', $payment) }}" class="ip-action" title="Ver detalle">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center ip-muted py-4">
                                        Aún no has realizado pagos en el portal.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($payments->hasPages())
                    <div class="ip-card-body">
                        {{ $payments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
