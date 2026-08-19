<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión · Control Escolar de Learning Play House</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="ip-login-split">

    {{-- Panel izquierdo: formulario de acceso --}}
    <section class="ip-login-left">
        <div class="ip-login-form-box">
            <div class="ip-login-form-brand">
                <img src="{{ asset('img/logo.jpg') }}" alt="Control Escolar de Learning Play House" class="ip-login-logo">
                <div>
                    <h1 class="ip-login-form-title">Iniciar sesión</h1>
                    <p class="ip-muted mb-0">Accede a tu panel de administración</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger ip-alert" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $errors->first() }}
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success ip-alert" role="alert">
                    <i class="bi bi-check-circle-fill me-1"></i>{{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf

                <div class="mb-3">
                    <label for="login" class="form-label">Usuario o correo electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" id="login" name="login"
                               class="form-control @error('login') is-invalid @enderror"
                               value="{{ old('login') }}" required autofocus autocomplete="username">
                    </div>
                    @error('login')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="password" class="form-label mb-0">Contraseña</label>
                        <span class="ip-form-hint">Mín. 8 caracteres</span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                        <input type="password" id="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               required autocomplete="current-password">
                    </div>
                    @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="form-check mb-4">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Recordar sesión</label>
                </div>

                <button type="submit" class="btn ip-btn w-100 py-2 ip-btn-lg">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Iniciar sesión
                </button>
            </form>

            <p class="ip-login-form-footer">© {{ date('Y') }} Control Escolar de Learning Play House · Todos los derechos reservados</p>
        </div>
    </section>

    {{-- Panel derecho: identidad del sistema --}}
    <aside class="ip-login-right">
        <div class="ip-login-hero">
            <img src="{{ asset('img/logo.jpg') }}" alt="Control Escolar de Learning Play House" class="ip-login-hero-logo">
            <h2 class="ip-login-hero-title">Control Escolar de Learning Play House</h2>
            <p class="ip-login-hero-subtitle">Sistema de administración para instituciones educativas</p>

            <ul class="ip-login-features">
                <li><i class="bi bi-buildings"></i><span>Gestión de escuelas y sucursales</span></li>
                <li><i class="bi bi-people"></i><span>Empleados y usuarios del sistema</span></li>
                <li><i class="bi bi-shield-lock"></i><span>Roles y control de accesos</span></li>
            </ul>

            <div class="ip-login-deco ip-login-deco-1"></div>
            <div class="ip-login-deco ip-login-deco-2"></div>
            <div class="ip-login-deco ip-login-deco-3"></div>
        </div>
    </aside>

</body>
</html>