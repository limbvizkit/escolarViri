@extends('layouts.app')

@section('title', 'Precargar pagos')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">
            Se toma como base <span class="fw-semibold">{{ $etiquetaMesActual }}</span> y el destino es
            <span class="fw-semibold">{{ $etiquetaMesSiguiente }}</span>.
            Marca las filas, edita lo que necesites y guarda.
        </p>
        <a href="{{ route('pagos.index') }}" class="btn ip-btn-outline">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>

    @if ($pagosSiguientes->isNotEmpty())
        <div class="ip-card mb-3">
            <div class="ip-card-header">
                <h5 class="ip-card-title">Pagos ya registrados para {{ $etiquetaMesSiguiente }}</h5>
            </div>

            <div class="ip-card-body ip-table-hint">
                <i class="bi bi-info-circle me-1"></i>Estos pagos ya existen y no se duplicarán; esas filas se omitirán al guardar.
            </div>

            <div class="table-responsive">
                <table class="table ip-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Alumno</th>
                            <th>Mes</th>
                            <th>Fecha</th>
                            <th>Entrada 8AM</th>
                            <th>Pronto pago</th>
                            <th>Pago normal</th>
                            <th>Talleres</th>
                            <th>Lunch</th>
                            <th>Forma de pago</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pagosSiguientes as $pago)
                            <tr>
                                <td>{{ $pago->id }}</td>
                                <td>
                                    <a href="{{ route('alumnos.show', $pago->alumno) }}" class="fw-semibold ip-link">{{ $pago->alumno->nombre_completo }}</a>
                                    <span class="badge text-bg-light ms-1">{{ $pago->alumno->gradoEscolar->nombre ?? '—' }}</span>
                                </td>
                                <td>{{ $pago->mes_label }}</td>
                                <td>{{ $pago->fecha?->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ $pago->entrada_8am !== null ? '$'.number_format((float) $pago->entrada_8am, 2) : '—' }}</td>
                                <td>{{ $pago->pronto_pago !== null ? '$'.number_format((float) $pago->pronto_pago, 2) : '—' }}</td>
                                <td>{{ $pago->pago_normal !== null ? '$'.number_format((float) $pago->pago_normal, 2) : '—' }}</td>
                                <td>{{ $pago->talleres !== null ? '$'.number_format((float) $pago->talleres, 2) : '—' }}</td>
                                <td>{{ $pago->lunch !== null ? '$'.number_format((float) $pago->lunch, 2) : '—' }}</td>
                                <td><span class="badge text-bg-light">{{ $pago->formaPago->nombre ?? '—' }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="ip-card">
        <div class="ip-card-header">
            <h5 class="ip-card-title">Pagos de {{ $etiquetaMesActual }}</h5>
        </div>

        @if ($pagosActuales->isEmpty())
            <div class="ip-card-body ip-muted py-4 text-center">
                No hay pagos registrados en {{ $etiquetaMesActual }} para precargar.
            </div>
        @else
            <form id="form-precargar" method="POST" action="{{ route('pagos.precargar.store') }}">
                @csrf

                <div class="table-responsive">
                    <table class="table ip-table mb-0">
                        <thead>
                            <tr>
                                <th class="text-center">
                                    <input type="checkbox" id="check-all" class="form-check-input" aria-label="Seleccionar todos los pagos">
                                </th>
                                <th>Alumno</th>
                                <th>Mes</th>
                                <th>Fecha</th>
                                <th>Entrada 8AM</th>
                                <th>Pronto pago</th>
                                <th>Pago normal</th>
                                <th>Talleres</th>
                                <th>Lunch</th>
                                <th>Forma de pago</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pagosActuales as $pago)
                                @php
                                    $i = $pago->id;
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="seleccionados[]" value="{{ $i }}"
                                               class="js-row-check form-check-input"
                                               aria-label="Seleccionar el pago de {{ $pago->alumno->nombre_completo }}">
                                    </td>

                                    <td>
                                        <a href="{{ route('alumnos.show', $pago->alumno) }}" class="fw-semibold ip-link">{{ $pago->alumno->nombre_completo }}</a>
                                        <span class="badge text-bg-light ms-1">{{ $pago->alumno->gradoEscolar->nombre ?? '—' }}</span>
                                    </td>

                                    <td>
                                        <input type="month" name="pagos[{{ $i }}][mes]"
                                               class="form-control form-control-sm"
                                               value="{{ old("pagos.$i.mes", $mesSiguiente) }}" required>
                                    </td>

                                    <td>
                                        <input type="date" name="pagos[{{ $i }}][fecha]"
                                               class="form-control form-control-sm"
                                               value="{{ old("pagos.$i.fecha", $pago->fecha?->copy()->addMonthNoOverflow()?->format('Y-m-d')) }}">
                                    </td>

                                    <td>
                                        <input type="number" step="0.01" min="0" name="pagos[{{ $i }}][entrada_8am]"
                                               class="form-control form-control-sm"
                                               value="{{ old("pagos.$i.entrada_8am", $pago->entrada_8am) }}">
                                    </td>

                                    <td>
                                        <input type="number" step="0.01" min="0" name="pagos[{{ $i }}][pronto_pago]"
                                               class="form-control form-control-sm"
                                               value="{{ old("pagos.$i.pronto_pago", $pago->pronto_pago) }}">
                                    </td>

                                    <td>
                                        <input type="number" step="0.01" min="0" name="pagos[{{ $i }}][pago_normal]"
                                               class="form-control form-control-sm"
                                               value="{{ old("pagos.$i.pago_normal", $pago->pago_normal) }}">
                                    </td>

                                    <td>
                                        <input type="number" step="0.01" min="0" name="pagos[{{ $i }}][talleres]"
                                               class="form-control form-control-sm"
                                               value="{{ old("pagos.$i.talleres", $pago->talleres) }}">
                                    </td>

                                    <td>
                                        <input type="number" step="0.01" min="0" name="pagos[{{ $i }}][lunch]"
                                               class="form-control form-control-sm"
                                               value="{{ old("pagos.$i.lunch", $pago->lunch) }}">
                                    </td>

                                    <td>
                                        <select name="pagos[{{ $i }}][forma_pago_id]" class="form-select form-select-sm">
                                            <option value="">— Sin forma —</option>
                                            @foreach ($formasPago as $forma)
                                                <option value="{{ $forma->id }}" {{ old("pagos.$i.forma_pago_id", $pago->forma_pago_id) == $forma->id ? 'selected' : '' }}>
                                                    {{ $forma->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="ip-form-actions">
                    <span id="resumen-seleccion" class="ip-muted me-auto">0 seleccionados</span>
                    <a href="{{ route('pagos.index') }}" class="btn ip-btn-outline">Cancelar</a>
                    <button type="submit" id="btn-guardar" class="btn ip-btn-success" disabled>
                        <i class="bi bi-save me-1"></i>Guardar seleccionados
                    </button>
                </div>
            </form>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';

            const form = document.getElementById('form-precargar');
            const checkAll = document.getElementById('check-all');
            const resumen = document.getElementById('resumen-seleccion');
            const btnGuardar = document.getElementById('btn-guardar');

            if (!form || !checkAll || !btnGuardar) return;

            const checks = Array.from(form.querySelectorAll('.js-row-check'));

            function contarSeleccionadas() {
                return checks.filter(function (c) { return c.checked; }).length;
            }

            function sincronizar() {
                const n = contarSeleccionadas();
                if (resumen) resumen.textContent = n + ' seleccionado(s)';
                btnGuardar.disabled = n === 0;
                checkAll.indeterminate = n > 0 && n < checks.length;
                if (!checkAll.indeterminate) {
                    checkAll.checked = n > 0 && n === checks.length;
                }
            }

            checkAll.addEventListener('change', function () {
                checks.forEach(function (c) { c.checked = checkAll.checked; });
                sincronizar();
            });

            checks.forEach(function (c) {
                c.addEventListener('change', sincronizar);
            });

            form.addEventListener('submit', function (e) {
                const n = contarSeleccionadas();
                if (n === 0) {
                    e.preventDefault();
                    return;
                }
                if (!confirm(`Se crearán ${n} pagos nuevos. ¿Continuar?`)) {
                    e.preventDefault();
                }
            });

            sincronizar();
        })();
    </script>
@endpush
