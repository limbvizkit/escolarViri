@extends('layouts.portal')

@section('title', 'Cambiar contraseña')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title mb-0">Cambia tu contraseña</h5>
                </div>
                <div class="ip-card-body">
                    <p class="ip-muted mb-3">
                        Es tu primer acceso. Para continuar, crea una contraseña nueva.
                    </p>

                    <form id="password-change-form" method="POST" action="{{ route('portal.password.change.update') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="password" class="form-label">Nueva contraseña <span class="ip-required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-key"></i></span>
                                <input type="password" id="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    required minlength="8" autocomplete="new-password">
                                <button type="button" class="input-group-text password-toggle" data-target="password" tabindex="-1" aria-label="Mostrar u ocultar contraseña">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Debe tener al menos 8 caracteres.</div>
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirmar nueva contraseña <span class="ip-required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    class="form-control @error('password') is-invalid @enderror"
                                    required minlength="8" autocomplete="new-password">
                                <button type="button" class="input-group-text password-toggle" data-target="password_confirmation" tabindex="-1" aria-label="Mostrar u ocultar confirmación de contraseña">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div id="password-match-feedback" class="small mt-1"></div>
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" id="submit-password" class="btn ip-btn-success w-100 py-2" disabled>
                            <i class="bi bi-check-lg me-1"></i>Guardar contraseña
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('password-change-form');
            const password = document.getElementById('password');
            const confirmation = document.getElementById('password_confirmation');
            const feedback = document.getElementById('password-match-feedback');
            const submitButton = document.getElementById('submit-password');

            function updateValidation() {
                const passwordValue = password.value;
                const confirmationValue = confirmation.value;

                password.classList.remove('is-valid', 'is-invalid');
                confirmation.classList.remove('is-valid', 'is-invalid');
                feedback.classList.remove('text-success', 'text-danger');

                const isPasswordValid = passwordValue.length >= 8;

                if (confirmationValue === '') {
                    feedback.textContent = '';
                    submitButton.disabled = true;
                    return;
                }

                if (passwordValue === confirmationValue) {
                    password.classList.add('is-valid');
                    confirmation.classList.add('is-valid');
                    feedback.textContent = 'Las contraseñas coinciden.';
                    feedback.classList.add('text-success');
                } else {
                    password.classList.add('is-invalid');
                    confirmation.classList.add('is-invalid');
                    feedback.textContent = 'Las contraseñas no coinciden.';
                    feedback.classList.add('text-danger');
                }

                submitButton.disabled = !isPasswordValid || passwordValue !== confirmationValue;
            }

            password.addEventListener('input', updateValidation);
            confirmation.addEventListener('input', updateValidation);

            form.addEventListener('submit', function (event) {
                if (password.value !== confirmation.value || password.value.length < 8) {
                    event.preventDefault();
                    updateValidation();
                }
            });

            document.querySelectorAll('.password-toggle').forEach(function (toggle) {
                toggle.addEventListener('click', function () {
                    const targetId = this.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const icon = this.querySelector('i');

                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.replace('bi-eye', 'bi-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.replace('bi-eye-slash', 'bi-eye');
                    }
                });
            });
        });
    </script>
@endpush
