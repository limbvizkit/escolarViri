@extends('layouts.portal')

@section('title', 'Realizar pago')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title mb-0">Realizar pago</h5>
                </div>
                <div class="ip-card-body">
                    @if (! $merchantId || ! $publicKey)
                        <div class="alert alert-warning ip-alert" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            El servicio de pagos no está configurado. Contacta al administrador para continuar.
                        </div>
                    @endif

                    <form id="payment-form" method="POST" action="{{ route('portal.payments.store') }}" novalidate>
                        @csrf
                        <input type="hidden" name="token_id" id="token_id">
                        <input type="hidden" name="device_session_id" id="device_session_id">
                        <input type="hidden" name="order_id" value="{{ $orderId }}">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="amount" class="form-label">Importe <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number"
                                           class="form-control @error('amount') is-invalid @enderror"
                                           id="amount"
                                           name="amount"
                                           min="1"
                                           max="100000"
                                           step="0.01"
                                           value="{{ old('amount') }}"
                                           placeholder="0.00"
                                           required>
                                </div>
                                @error('amount')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="concepto" class="form-label">Concepto <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('concepto') is-invalid @enderror"
                                       id="concepto"
                                       name="concepto"
                                       value="{{ old('concepto') }}"
                                       placeholder="Ej. Colegiatura marzo"
                                       maxlength="255"
                                       required>
                                @error('concepto')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <hr class="my-2">
                                <h6 class="ip-card-title">Datos de la tarjeta</h6>
                                <p class="ip-muted small">
                                    Tu número de tarjeta y CVV se tokenizan directamente con OpenPay y nunca llegan a nuestro servidor.
                                </p>
                            </div>

                            <div class="col-12">
                                <label for="card_number" class="form-label">Número de tarjeta <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control"
                                       id="card_number"
                                       maxlength="19"
                                       placeholder="0000 0000 0000 0000"
                                       autocomplete="cc-number"
                                       inputmode="numeric"
                                       @if (! $merchantId || ! $publicKey) disabled @endif>
                            </div>

                            <div class="col-12">
                                <label for="holder_name" class="form-label">Nombre del titular <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control"
                                       id="holder_name"
                                       placeholder="Como aparece en la tarjeta"
                                       autocomplete="cc-name"
                                       @if (! $merchantId || ! $publicKey) disabled @endif>
                            </div>

                            <div class="col-md-6">
                                <label for="expiration_month" class="form-label">Mes de expiración <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control"
                                       id="expiration_month"
                                       maxlength="2"
                                       placeholder="MM"
                                       inputmode="numeric"
                                       autocomplete="cc-exp-month"
                                       @if (! $merchantId || ! $publicKey) disabled @endif>
                            </div>

                            <div class="col-md-6">
                                <label for="expiration_year" class="form-label">Año de expiración <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control"
                                       id="expiration_year"
                                       maxlength="2"
                                       placeholder="AA"
                                       inputmode="numeric"
                                       autocomplete="cc-exp-year"
                                       @if (! $merchantId || ! $publicKey) disabled @endif>
                            </div>

                            <div class="col-md-6">
                                <label for="cvv2" class="form-label">CVV <span class="text-danger">*</span></label>
                                <input type="password"
                                       class="form-control"
                                       id="cvv2"
                                       maxlength="4"
                                       placeholder="123"
                                       inputmode="numeric"
                                       autocomplete="cc-csc"
                                       @if (! $merchantId || ! $publicKey) disabled @endif>
                            </div>
                        </div>

                        <div id="payment-errors" class="alert alert-danger ip-alert d-none mt-3" role="alert"></div>

                        <div class="ip-form-actions mt-4">
                            <a href="{{ route('portal.dashboard') }}" class="btn ip-btn-outline">Cancelar</a>
                            <button type="submit" class="btn ip-btn-success" id="submit-button" @if (! $merchantId || ! $publicKey) disabled @endif>
                                <span class="spinner-border spinner-border-sm d-none me-1" id="submit-spinner" aria-hidden="true"></span>
                                <i class="bi bi-lock-fill me-1"></i>Pagar ahora
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if ($merchantId && $publicKey)
        <script src="https://js.openpay.mx/openpay.v1.min.js"></script>
        <script src="https://js.openpay.mx/openpay-data.v1.min.js"></script>
        <script>
            (function () {
                const merchantId = @json($merchantId);
                const publicKey = @json($publicKey);
                const isSandbox = @json($sandbox);

                OpenPay.setId(merchantId);
                OpenPay.setApiKey(publicKey);
                OpenPay.setSandboxMode(isSandbox);

                const deviceSessionId = OpenPay.deviceData.setup('payment-form', 'device_session_id');

                const form = document.getElementById('payment-form');
                const submitButton = document.getElementById('submit-button');
                const spinner = document.getElementById('submit-spinner');
                const errorContainer = document.getElementById('payment-errors');

                function showError(message) {
                    errorContainer.textContent = message;
                    errorContainer.classList.remove('d-none');
                }

                function clearError() {
                    errorContainer.classList.add('d-none');
                    errorContainer.textContent = '';
                }

                function setLoading(loading) {
                    submitButton.disabled = loading;
                    spinner.classList.toggle('d-none', !loading);
                }

                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    clearError();
                    setLoading(true);

                    const tokenData = {
                        card_number: document.getElementById('card_number').value.replace(/\s/g, ''),
                        holder_name: document.getElementById('holder_name').value.trim(),
                        expiration_year: document.getElementById('expiration_year').value.trim(),
                        expiration_month: document.getElementById('expiration_month').value.trim(),
                        cvv2: document.getElementById('cvv2').value.trim(),
                    };

                    OpenPay.token.create(tokenData, function (response) {
                        document.getElementById('token_id').value = response.data.id;
                        document.getElementById('device_session_id').value = deviceSessionId;
                        form.submit();
                    }, function (error) {
                        setLoading(false);
                        const message = error && error.data && error.data.description
                            ? error.data.description
                            : 'No pudimos tokenizar la tarjeta. Verifica los datos e intenta de nuevo.';
                        showError(message);
                    });

                    return false;
                });
            })();
        </script>
    @endif
@endpush
