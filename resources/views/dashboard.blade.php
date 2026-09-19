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
                    <h5 class="ip-card-title">Adeudos pendientes</h5>
                    <div>
                        <label for="adeudosModo" class="visually-hidden">Modo del gráfico</label>
                        <select id="adeudosModo" class="form-select form-select-sm">
                            <option value="grado">Por grado escolar</option>
                            <option value="alumno">Por alumno</option>
                        </select>
                    </div>
                </div>
                <div class="ip-card-body">
                    <div id="adeudosChartWrap" class="ip-chart">
                        <canvas id="chartAdeudosPendientes"></canvas>
                    </div>
                    <div id="adeudosEmpty" class="text-center ip-muted py-5 d-none">
                        No hay adeudos pendientes por <span id="adeudosEmptyMode">grado escolar</span>.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const charts = @json($charts, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

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

            (function () {
                const wrap = document.getElementById('adeudosChartWrap');
                const empty = document.getElementById('adeudosEmpty');
                const emptyMode = document.getElementById('adeudosEmptyMode');
                const select = document.getElementById('adeudosModo');
                const ctx = document.getElementById('chartAdeudosPendientes').getContext('2d');

                let chart = null;

                const datasets = {
                    grado: {
                        labels: charts.adeudosPorGrado.labels,
                        data: charts.adeudosPorGrado.data,
                        label: 'Saldo pendiente',
                        axis: 'x',
                    },
                    alumno: {
                        labels: charts.adeudosPorAlumno.labels,
                        data: charts.adeudosPorAlumno.data,
                        label: 'Saldo pendiente',
                        axis: 'y',
                    },
                };

                const render = (modo) => {
                    const dataset = datasets[modo];

                    if (chart) {
                        chart.destroy();
                        chart = null;
                    }

                    if (dataset.data.length === 0) {
                        wrap.classList.add('d-none');
                        empty.classList.remove('d-none');
                        emptyMode.textContent = modo === 'alumno' ? 'alumno' : 'grado escolar';

                        return;
                    }

                    wrap.classList.remove('d-none');
                    empty.classList.add('d-none');

                    const isHorizontal = modo === 'alumno';

                    chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: dataset.labels,
                            datasets: [{
                                label: dataset.label,
                                data: dataset.data,
                                backgroundColor: colors[5],
                                borderRadius: 6,
                            }],
                        },
                        options: {
                            indexAxis: isHorizontal ? 'y' : 'x',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: (context) => ' ' + formatMoney(isHorizontal ? context.parsed.x : context.parsed.y),
                                    },
                                },
                            },
                            scales: {
                                [isHorizontal ? 'x' : 'y']: {
                                    beginAtZero: true,
                                    ticks: { callback: (value) => formatMoney(value) },
                                },
                            },
                        },
                    });
                };

                select.addEventListener('change', (event) => render(event.target.value));

                render(select.value);
            })();
        })();
    </script>
@endpush
