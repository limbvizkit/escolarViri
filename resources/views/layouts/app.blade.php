<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Control Escolar') · Control Escolar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}" rel="stylesheet">
</head>
<body>
    <script>
        (function () {
            try {
                if (localStorage.getItem('ip-sidebar-collapsed') === '1') {
                    document.body.classList.add('sidebar-collapsed');
                }
            } catch (e) {}
        })();
    </script>
    <aside class="ip-sidebar" id="ipSidebar">
        <div class="ip-sidebar-top">
            <div class="ip-brand">
                <span class="ip-brand-icon"><i class="bi bi-mortarboard-fill"></i></span>
                <span>Control Escolar</span>
            </div>
            <button type="button" class="ip-sidebar-toggle" id="ipSidebarToggle" aria-label="Ocultar barra lateral" title="Ocultar barra lateral">
                <i class="bi bi-chevron-left"></i>
            </button>
        </div>

        <nav class="ip-nav">
            <div class="ip-nav-title">Principal</div>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span>
            </a>

            <div class="ip-nav-title">Catálogos</div>
            <a href="{{ route('escuelas.index') }}" class="{{ request()->routeIs('escuelas.*') ? 'active' : '' }}">
                <i class="bi bi-buildings-fill"></i><span>Escuelas</span>
            </a>
            <a href="{{ route('sucursales.index') }}" class="{{ request()->routeIs('sucursales.*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3-fill"></i><span>Sucursales</span>
            </a>
            <a href="{{ route('empleados.index') }}" class="{{ request()->routeIs('empleados.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i><span>Empleados</span>
            </a>

            <div class="ip-nav-title">Académico</div>
            <a href="{{ route('alumnos.index') }}" class="{{ request()->routeIs('alumnos.*') ? 'active' : '' }}">
                <i class="bi bi-person-lines-fill"></i><span>Alumnos</span>
            </a>
            <a href="{{ route('grados-escolares.index') }}" class="{{ request()->routeIs('grados-escolares.*') ? 'active' : '' }}">
                <i class="bi bi-layers-fill"></i><span>Grados Escolares</span>
            </a>
            <a href="{{ route('pagos.index') }}" class="{{ request()->routeIs('pagos.*') ? 'active' : '' }}">
                <i class="bi bi-cash-coin"></i><span>Pagos</span>
            </a>
            <a href="{{ route('talleres.index') }}" class="{{ request()->routeIs('talleres.*') ? 'active' : '' }}">
                <i class="bi bi-easel"></i><span>Talleres</span>
            </a>

            <div class="ip-nav-title">Acceso</div>
            <a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge-fill"></i><span>Usuarios</span>
            </a>
            <a href="{{ route('roles.index') }}" class="{{ request()->routeIs('roles.*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock-fill"></i><span>Roles</span>
            </a>
        </nav>
    </aside>

    <main class="ip-main">
        <div class="ip-topbar">
            <h1 class="ip-heading">@yield('title', 'Dashboard')</h1>
            <div class="d-flex align-items-center gap-2">
                <span class="ip-user-chip">
                    <span class="ip-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                    {{ auth()->user()->name ?? 'Invitado' }}
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn ip-btn-outline btn-sm" title="Cerrar sesión">
                        <i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión
                    </button>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success ip-alert alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-1"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger ip-alert alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            var toggle = document.getElementById('ipSidebarToggle');
            if (!toggle) return;
            var icon = toggle.querySelector('i');

            function sync() {
                var collapsed = document.body.classList.contains('sidebar-collapsed');
                var label = collapsed ? 'Mostrar barra lateral' : 'Ocultar barra lateral';
                toggle.setAttribute('aria-label', label);
                toggle.setAttribute('title', label);
                if (icon) {
                    icon.classList.toggle('bi-chevron-left', !collapsed);
                    icon.classList.toggle('bi-chevron-right', collapsed);
                }
            }

            sync();

            toggle.addEventListener('click', function () {
                var collapsed = document.body.classList.toggle('sidebar-collapsed');
                try {
                    localStorage.setItem('ip-sidebar-collapsed', collapsed ? '1' : '0');
                } catch (e) {}
                sync();
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>