@extends('layouts.portal')

@section('title', 'Iniciar sesión')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title mb-0">Acceder al portal</h5>
                </div>
                <div class="ip-card-body">
                    <form method="POST" action="{{ route('portal.login.attempt') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico <span class="ip-required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" id="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" required autofocus autocomplete="email">
                            </div>
                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Contraseña <span class="ip-required">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                <input type="password" id="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    required autocomplete="current-password">
                            </div>
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn ip-btn w-100 py-2">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Iniciar sesión
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('portal.register') }}" class="ip-muted small">¿No tienes cuenta? Regístrate</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
