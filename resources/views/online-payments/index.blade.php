@extends('layouts.app')

@section('title', 'Pagos en línea')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Pagos procesados a través del portal</p>
        <div class="d-flex gap-2">
            @php
                $exportQuery = array_filter(request()->only(['q', 'estatus', 'fecha_desde', 'fecha_hasta', 'sort', 'direction']), fn ($v) => $v !== null && $v !== '');
            @endphp
            <a href="{{ route('online-payments.export.pdf', $exportQuery) }}" class="btn ip-btn-danger btn-sm">
                <i class="bi bi-file-earmark-pdf me-1"></i>PDF
            </a>
            <a href="{{ route('online-payments.export.excel', $exportQuery) }}" class="btn ip-btn-success btn-sm">
                <i class="bi bi-file-earmark-excel me-1"></i>Excel
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('online-payments.index') }}" class="ip-filters mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label ip-filters-label" for="q">Buscar</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="q" name="q" class="form-control" value="{{ request('q') }}" placeholder="Concepto, usuario, orden, charge...">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label ip-filters-label" for="estatus">Estatus</label>
                <select name="estatus" id="estatus" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach ([\App\Models\PagoOnline::STATUS_PENDING => 'Pendiente', \App\Models\PagoOnline::STATUS_COMPLETED => 'Completado', \App\Models\PagoOnline::STATUS_FAILED => 'Fallido'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('estatus') == $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label ip-filters-label" for="fecha_desde">Desde</label>
                <input type="date" id="fecha_desde" name="fecha_desde" class="form-control form-control-sm" value="{{ request('fecha_desde') }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label ip-filters-label" for="fecha_hasta">Hasta</label>
                <input type="date" id="fecha_hasta" name="fecha_hasta" class="form-control form-control-sm" value="{{ request('fecha_hasta') }}">
            </div>
            <div class="col-6 col-md-auto">
                <label class="form-label ip-filters-label" for="per_page">Mostrar</label>
                <select name="per_page" id="per_page" class="form-select form-select-sm">
                    @foreach ([10, 15, 25, 50] as $opt)
                        <option value="{{ $opt }}" @selected((int) request('per_page', 10) === $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-auto d-flex gap-2 mt-2 mt-md-0">
                <button type="submit" class="btn ip-btn btn-sm">
                    <i class="bi bi-funnel-fill me-1"></i>Filtrar
                </button>
                @if (request()->filled('q') || request()->filled('estatus') || request()->filled('fecha_desde') || request()->filled('fecha_hasta'))
                    <a href="{{ route('online-payments.index') }}" class="btn ip-btn-outline btn-sm">
                        <i class="bi bi-x-lg me-1"></i>Limpiar
                    </a>
                @endif
            </div>
            <input type="hidden" name="sort" value="{{ request('sort') }}">
            <input type="hidden" name="direction" value="{{ request('direction') }}">
        </div>
    </form>

    <div class="ip-card">
        <div class="ip-card-header">
            <span class="ip-table-summary">Mostrando {{ $payments->currentPage() }} de {{ $payments->lastPage() }}</span>
            <h5 class="ip-card-title">Listado de pagos en línea</h5>
        </div>

        <div class="table-responsive">
            <table class="table ip-table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th>Concepto</th>
                        <th class="text-end">Monto</th>
                        <th>Order ID</th>
                        <th>OpenPay ID</th>
                        <th>Estatus</th>
                        <th>Fecha</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td>{{ $payment->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $payment->portalUser->name ?? '—' }}</div>
                                <div class="ip-muted small">{{ $payment->portalUser->email ?? '' }}</div>
                            </td>
                            <td>{{ $payment->concepto }}</td>
                            <td class="text-end">${{ number_format((float) $payment->monto, 2) }} {{ $payment->moneda }}</td>
                            <td class="ip-muted">{{ $payment->order_id }}</td>
                            <td class="ip-muted">{{ $payment->openpay_charge_id ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $payment->statusBadgeClass() }}">
                                    {{ $payment->statusLabel() }}
                                </span>
                            </td>
                            <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('online-payments.show', $payment) }}" class="ip-action" title="Ver">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center ip-muted py-4">
                                No hay pagos en línea registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ip-card-body d-flex justify-content-center">
            {{ $payments->links() }}
        </div>
    </div>
@endsection
