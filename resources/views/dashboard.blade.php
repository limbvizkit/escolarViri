@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="ip-card ip-stat">
                <div class="ip-stat-icon" style="background:#eaf1ff;color:var(--ip-primary);">
                    <i class="bi bi-buildings"></i>
                </div>
                <div>
                    <div class="ip-stat-number">{{ $stats['escuelas'] }}</div>
                    <div class="ip-stat-label">Escuelas</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="ip-card ip-stat">
                <div class="ip-stat-icon" style="background:#f1eaff;color:var(--ip-accent);">
                    <i class="bi bi-diagram-3"></i>
                </div>
                <div>
                    <div class="ip-stat-number">{{ $stats['sucursales'] }}</div>
                    <div class="ip-stat-label">Sucursales</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="ip-card ip-stat">
                <div class="ip-stat-icon" style="background:#e6f6ee;color:var(--ip-success);">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <div class="ip-stat-number">{{ $stats['empleados'] }}</div>
                    <div class="ip-stat-label">Empleados</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="ip-card ip-stat">
                <div class="ip-stat-icon" style="background:#fff4e6;color:#fd7e14;">
                    <i class="bi bi-person-lines-fill"></i>
                </div>
                <div>
                    <div class="ip-stat-number">{{ $stats['alumnos'] }}</div>
                    <div class="ip-stat-label">Alumnos</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="ip-card ip-stat">
                <div class="ip-stat-icon" style="background:#e6f4ff;color:#0ea5e9;">
                    <i class="bi bi-layers"></i>
                </div>
                <div>
                    <div class="ip-stat-number">{{ $stats['gradosEscolares'] }}</div>
                    <div class="ip-stat-label">Grados Escolares</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="ip-card ip-stat">
                <div class="ip-stat-icon" style="background:#ffeef2;color:var(--ip-danger);">
                    <i class="bi bi-person-badge"></i>
                </div>
                <div>
                    <div class="ip-stat-number">{{ $stats['usuarios'] }}</div>
                    <div class="ip-stat-label">Usuarios</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="ip-card ip-stat">
                <div class="ip-stat-icon" style="background:#f1eaff;color:var(--ip-accent);">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <div>
                    <div class="ip-stat-number">{{ $stats['roles'] }}</div>
                    <div class="ip-stat-label">Roles</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Alumnos por grado escolar</h5>
                </div>
                <div class="ip-card-body">
                    <div class="ip-chart">
                        <canvas id="chartAlumnosGradoEscolar"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Ingresos por mes</h5>
                </div>
                <div class="ip-card-body">
                    <div class="ip-chart">
                        <canvas id="chartIngresosMes"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Pagos por forma de pago</h5>
                </div>
                <div class="ip-card-body">
                    <div class="ip-chart">
                        <canvas id="chartFormasPago"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Top pagos por alumno</h5>
                </div>
                <div class="ip-card-body">
                    <div class="ip-chart">
                        <canvas id="chartTopAlumnos"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Escuelas recientes</h5>
                    <a href="{{ route('escuelas.index') }}" class="ip-action" title="Ver todas">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse ($recientes['escuelas'] as $escuela)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $escuela->nombre }}</div>
                                <small class="ip-muted">{{ $escuela->clave }}</small>
                            </div>
                            <span class="badge ip-badge-{{ $escuela->estatus ? 'active' : 'inactive' }}">
                                {{ $escuela->estatus ? 'Activo' : 'Inactivo' }}
                            </span>
                        </li>
                    @empty
                        <li class="list-group-item ip-muted">Sin escuelas registradas.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Sucursales recientes</h5>
                    <a href="{{ route('sucursales.index') }}" class="ip-action" title="Ver todas">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse ($recientes['sucursales'] as $sucursal)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $sucursal->nombre }}</div>
                                <small class="ip-muted">{{ $sucursal->escuela->nombre ?? 'Sin escuela' }}</small>
                            </div>
                            <span class="badge ip-badge-{{ $sucursal->estatus ? 'active' : 'inactive' }}">
                                {{ $sucursal->estatus ? 'Activo' : 'Inactivo' }}
                            </span>
                        </li>
                    @empty
                        <li class="list-group-item ip-muted">Sin sucursales registradas.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Empleados recientes</h5>
                    <a href="{{ route('empleados.index') }}" class="ip-action" title="Ver todos">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse ($recientes['empleados'] as $empleado)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $empleado->nombre_completo }}</div>
                                <small class="ip-muted">{{ $empleado->sucursal->nombre ?? 'Sin sucursal' }}</small>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item ip-muted">Sin empleados registrados.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Alumnos recientes</h5>
                    <a href="{{ route('alumnos.index') }}" class="ip-action" title="Ver todos">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse ($recientes['alumnos'] as $alumno)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $alumno->nombre_completo }}</div>
                                <small class="ip-muted">{{ $alumno->gradoEscolar->nombre ?? 'Sin grado escolar' }}</small>
                            </div>
                            <span class="badge"
                                  style="background:#eaf1ff;color:var(--ip-primary);font-weight:600;">
                                {{ $alumno->horario ?? '—' }}
                            </span>
                        </li>
                    @empty
                        <li class="list-group-item ip-muted">Sin alumnos registrados.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const charts = @json($charts);

            const formatMoney = (value) =>
                '$' + value.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            const colors = ['#0d6efd', '#6f42c1', '#198754', '#fd7e14', '#0ea5e9', '#dc3545'];

            new Chart(document.getElementById('chartAlumnosGradoEscolar'), {
                type: 'bar',
                data: {
                    labels: charts.alumnosPorGradoEscolar.labels,
                    datasets: [{
                        label: 'Alumnos',
                        data: charts.alumnosPorGradoEscolar.data,
                        backgroundColor: colors[0],
                        borderRadius: 6,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                        },
                    },
                },
            });

            new Chart(document.getElementById('chartIngresosMes'), {
                type: 'line',
                data: {
                    labels: charts.ingresosPorMes.labels,
                    datasets: [{
                        label: 'Ingresos',
                        data: charts.ingresosPorMes.data,
                        borderColor: colors[0],
                        backgroundColor: 'rgba(13, 110, 253, 0.12)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointBackgroundColor: colors[0],
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => ' ' + formatMoney(context.parsed.y),
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { callback: (value) => formatMoney(value) },
                        },
                    },
                },
            });

            new Chart(document.getElementById('chartFormasPago'), {
                type: 'doughnut',
                data: {
                    labels: charts.pagosPorFormaPago.labels,
                    datasets: [{
                        data: charts.pagosPorFormaPago.data,
                        backgroundColor: colors,
                        borderColor: '#ffffff',
                        borderWidth: 2,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: (context) => ' ' + context.label + ': ' + formatMoney(context.parsed),
                            },
                        },
                    },
                },
            });

            new Chart(document.getElementById('chartTopAlumnos'), {
                type: 'bar',
                data: {
                    labels: charts.topPagosPorAlumno.labels,
                    datasets: [{
                        label: 'Total',
                        data: charts.topPagosPorAlumno.data,
                        backgroundColor: colors[2],
                        borderRadius: 6,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => ' ' + formatMoney(context.parsed.x),
                            },
                        },
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { callback: (value) => formatMoney(value) },
                        },
                    },
                },
            });
        })();
    </script>
@endpush