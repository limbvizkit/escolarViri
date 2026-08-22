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
                <i class="bi bi-pencil-fill me-1"></i>Haz clic en el lápiz de una fila para editar en la tabla las columnas que quieras.
            </div>

            <div class="table-responsive">
                <table class="table ip-table ip-table-zebra mb-0" id="pagos-siguientes-table">
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Mes</th>
                            <th>Fecha</th>
                            <th>Entrada 8AM</th>
                            <th>Pronto pago</th>
                            <th>Pago normal</th>
                            <th>Talleres</th>
                            <th>Lunch</th>
                            <th>Forma de pago</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pagosSiguientes as $pago)
                            @php
                                $formId = 'inline-' . $pago->id;
                                $montos = [
                                    'entrada_8am' => $pago->entrada_8am,
                                    'pronto_pago' => $pago->pronto_pago,
                                    'pago_normal' => $pago->pago_normal,
                                ];
                            @endphp
                            <tr class="ip-inline-row" data-row="{{ $pago->id }}">
                                <td>
                                    <div class="cell-view" data-target="alumno_id">
                                        <a href="{{ route('alumnos.show', $pago->alumno) }}" class="fw-semibold ip-link">{{ $pago->alumno->nombre_completo }}</a>
                                        <span class="badge text-bg-light ms-1">{{ $pago->alumno->gradoEscolar->nombre ?? '—' }}</span>
                                    </div>
                                </td>

                                <td>
                                    <div class="cell-view fw-semibold" data-target="mes">{{ $pago->mes_label }}</div>
                                    <input type="month" name="mes" form="{{ $formId }}" data-key="mes" data-format="mes"
                                           class="form-control form-control-sm cell-edit d-none"
                                           value="{{ $pago->mes }}" data-original="{{ $pago->mes }}">
                                </td>

                                <td>
                                    <div class="cell-view" data-target="fecha">{{ $pago->fecha?->format('d/m/Y') ?? '—' }}</div>
                                    <input type="date" name="fecha" form="{{ $formId }}" data-key="fecha" data-format="date"
                                           class="form-control form-control-sm cell-edit d-none"
                                           value="{{ $pago->fecha?->format('Y-m-d') }}"
                                           data-original="{{ $pago->fecha?->format('Y-m-d') }}">
                                </td>

                                @foreach ($montos as $campo => $monto)
                                    <td>
                                        <div class="cell-view" data-target="{{ $campo }}">
                                            {{ $monto !== null ? '$' . number_format((float) $monto, 2) : '—' }}
                                        </div>
                                        <div class="input-group input-group-sm cell-edit d-none">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.01" min="0" name="{{ $campo }}" form="{{ $formId }}"
                                                   data-key="{{ $campo }}" data-format="money"
                                                   class="form-control" value="{{ $monto }}" data-original="{{ $monto }}">
                                        </div>
                                    </td>
                                @endforeach

                                <td>
                                    <div class="cell-view" data-target="talleres">
                                        {{ $pago->talleres !== null ? '$' . number_format((float) $pago->talleres, 2) : '—' }}
                                    </div>
                                    <div class="input-group input-group-sm cell-edit d-none">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" min="0" name="talleres" form="{{ $formId }}"
                                               data-key="talleres" data-format="money"
                                               class="form-control" value="{{ $pago->talleres }}" data-original="{{ $pago->talleres }}">
                                    </div>
                                </td>

                                <td>
                                    <div class="cell-view" data-target="lunch">
                                        {{ $pago->lunch !== null ? '$' . number_format((float) $pago->lunch, 2) : '—' }}
                                    </div>
                                    <div class="input-group input-group-sm cell-edit d-none">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" min="0" name="lunch" form="{{ $formId }}"
                                               data-key="lunch" data-format="money"
                                               class="form-control" value="{{ $pago->lunch }}" data-original="{{ $pago->lunch }}">
                                    </div>
                                </td>

                                <td>
                                    @php
                                        $formaPagoSuperior = $pago->formaPago;
                                        $formaClaseSuperior = $formaPagoSuperior
                                            ? 'ip-forma-pago-'.$formaPagoSuperior->id.' ip-forma-pago-' . \Illuminate\Support\Str::slug($formaPagoSuperior->nombre)
                                            : 'ip-forma-pago-none';
                                    @endphp
                                    <div class="cell-view" data-target="forma_pago_id">
                                        <span class="badge ip-badge-forma-pago {{ $formaClaseSuperior }}">{{ $formaPagoSuperior?->nombre ?? '—' }}</span>
                                    </div>
                                    <select name="forma_pago_id" form="{{ $formId }}" data-key="forma_pago_id" data-format="forma"
                                            class="form-select form-select-sm cell-edit d-none"
                                            data-original="{{ $pago->forma_pago_id }}">
                                        <option value="">— Sin forma —</option>
                                        @foreach ($formasPago as $forma)
                                            <option value="{{ $forma->id }}" {{ $pago->forma_pago_id == $forma->id ? 'selected' : '' }}>
                                                {{ $forma->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="text-end">
                                    <form id="{{ $formId }}" method="POST" action="{{ route('pagos.inline-update', $pago) }}">
                                        @csrf
                                        @method('PUT')
                                    </form>
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" class="ip-action js-inline-toggle" title="Editar en la tabla">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button type="button" class="ip-action ip-action-success d-none js-inline-save" title="Guardar cambios">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button type="button" class="ip-action d-none js-inline-cancel" title="Cancelar">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                        <a href="{{ route('pagos.edit', $pago) }}" class="ip-action" title="Editar (página)">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    </div>
                                </td>
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

                <div class="ip-form-actions ip-form-actions-top border-bottom-0 mb-0 pb-2">
                    <span class="ip-muted js-resumen-seleccion">0 seleccionado(s)</span>
                    <button type="submit" class="btn ip-btn-success js-btn-guardar" disabled>
                        <i class="bi bi-save me-1"></i>Guardar seleccionados
                    </button>
                </div>

                <div class="ip-table-scroll">
                    <table class="table ip-table ip-table-zebra mb-0" id="pagos-actuales-table">
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
                                                   value="{{ old('pagos.'.$i.'.mes', $mesSiguiente) }}" required>
                                        </td>

                                        <td>
                                            <input type="date" name="pagos[{{ $i }}][fecha]"
                                                   class="form-control form-control-sm"
                                                   value="{{ old('pagos.'.$i.'.fecha', $pago->fecha?->copy()->addMonthNoOverflow()?->format('Y-m-d')) }}">
                                        </td>

                                        <td>
                                            <input type="number" step="0.01" min="0" name="pagos[{{ $i }}][entrada_8am]"
                                                   class="form-control form-control-sm"
                                                   value="{{ old('pagos.'.$i.'.entrada_8am', $pago->entrada_8am) }}">
                                        </td>

                                        <td>
                                            <input type="number" step="0.01" min="0" name="pagos[{{ $i }}][pronto_pago]"
                                                   class="form-control form-control-sm"
                                                   value="{{ old('pagos.'.$i.'.pronto_pago', $pago->pronto_pago) }}">
                                        </td>

                                        <td>
                                            <input type="number" step="0.01" min="0" name="pagos[{{ $i }}][pago_normal]"
                                                   class="form-control form-control-sm"
                                                   value="{{ old('pagos.'.$i.'.pago_normal', $pago->pago_normal) }}">
                                        </td>

                                        <td>
                                            <input type="number" step="0.01" min="0" name="pagos[{{ $i }}][talleres]"
                                                   class="form-control form-control-sm"
                                                   value="{{ old('pagos.'.$i.'.talleres', $pago->talleres) }}">
                                        </td>

                                        <td>
                                            <input type="number" step="0.01" min="0" name="pagos[{{ $i }}][lunch]"
                                                   class="form-control form-control-sm"
                                                   value="{{ old('pagos.'.$i.'.lunch', $pago->lunch) }}">
                                        </td>

                                        <td>
                                            <select name="pagos[{{ $i }}][forma_pago_id]" class="form-select form-select-sm">
                                                <option value="">— Sin forma —</option>
                                                @foreach ($formasPago as $forma)
                                                    <option value="{{ $forma->id }}" {{ old('pagos.'.$i.'.forma_pago_id', $pago->forma_pago_id) == $forma->id ? 'selected' : '' }}>
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
                    <span class="ip-muted me-auto js-resumen-seleccion">0 seleccionado(s)</span>
                    <a href="{{ route('pagos.index') }}" class="btn ip-btn-outline">Cancelar</a>
                    <button type="submit" class="btn ip-btn-success js-btn-guardar" disabled>
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

            const MESES = [
                'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
            ];

            function formatearFecha(v) {
                if (!v) return '—';
                const partes = v.split('-');
                return partes.length === 3 ? partes[2] + '/' + partes[1] + '/' + partes[0] : v;
            }

            function etiquetaMes(v) {
                if (!v) return '—';
                const partes = v.split('-');
                if (partes.length === 2 && partes[1] >= '01' && partes[1] <= '12') {
                    return MESES[parseInt(partes[1], 10) - 1] + ' ' + partes[0];
                }
                return v;
            }

            function crearContenedorAlertas() {
                let cont = document.getElementById('ip-inline-alerts');
                if (!cont) {
                    cont = document.createElement('div');
                    cont.id = 'ip-inline-alerts';
                    cont.className = 'mb-3';
                    const main = document.querySelector('main');
                    if (main) main.insertBefore(cont, main.firstChild);
                }
                return cont;
            }

            function mostrarAlerta(mensaje, tipo) {
                const cont = crearContenedorAlertas();
                const a = document.createElement('div');
                a.className = 'alert alert-' + (tipo || 'danger') + ' ip-alert alert-dismissible fade show';
                a.setAttribute('role', 'alert');
                a.innerHTML = '<i class="bi bi-' + (tipo === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill') + ' me-1"></i>' + mensaje +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>';
                cont.prepend(a);
                setTimeout(() => a.remove(), 6000);
            }

            function renderView(row) {
                row.querySelectorAll('[data-key]').forEach(function (inp) {
                    const key = inp.dataset.key;
                    const view = row.querySelector('[data-target="' + key + '"]');
                    if (!view) return;
                    const fmt = inp.dataset.format;
                    const val = inp.value;
                    if (fmt === 'date') {
                        view.textContent = formatearFecha(val);
                    } else if (fmt === 'mes') {
                        view.textContent = etiquetaMes(val);
                    } else if (fmt === 'money') {
                        view.textContent = val === '' || val === null ? '—' : '$' + Number(val).toFixed(2);
                    } else if (fmt === 'forma') {
                        const badge = view.querySelector('span');
                        if (badge) {
                            badge.textContent = inp.options[inp.selectedIndex].text;
                        } else {
                            view.textContent = inp.options[inp.selectedIndex].text;
                        }
                    } else {
                        view.textContent = val === '' || val === null ? '—' : val;
                    }
                });
            }

            function setModoEdicion(row, editando) {
                row.classList.toggle('is-editing', editando);
                row.querySelectorAll('.cell-view').forEach(c => {
                    const target = c.dataset.target;
                    const esEditable = target && row.querySelector('[data-key="' + target + '"]');

                    if (esEditable) {
                        c.classList.toggle('d-none', editando);
                    }
                });
                row.querySelectorAll('.cell-edit').forEach(c => c.classList.toggle('d-none', !editando));
                row.querySelector('.js-inline-toggle')?.classList.toggle('d-none', editando);
                row.querySelector('.js-inline-save')?.classList.toggle('d-none', !editando);
                row.querySelector('.js-inline-cancel')?.classList.toggle('d-none', !editando);
                if (editando) {
                    row.querySelectorAll('[data-key]').forEach(i => i.disabled = false);
                    const primero = row.querySelector('input[data-key], select[data-key]');
                    if (primero) primero.focus();
                }
            }

            function guardarFila(row) {
                const form = row.querySelector('form[id^="inline-"]');
                if (!form) return;
                row.querySelectorAll('[data-key]').forEach(inp => {
                    inp.disabled = (inp.dataset.original || '') === (inp.value || '');
                });
                const guardarBtn = row.querySelector('.js-inline-save');

                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                }).then(async (resp) => {
                    const datos = await resp.json().catch(() => ({}));
                    if (!resp.ok) {
                        const errs = datos.errors || {};
                        const lista = Object.values(errs).flat().join('<br>') ||
                            (datos.mensaje || 'No se pudo guardar.');
                        mostrarAlerta(lista, 'danger');
                        return;
                    }
                    row.querySelectorAll('[data-key]').forEach(inp => {
                        inp.dataset.original = inp.value;
                    });
                    const valores = datos.valores || {};
                    row.querySelectorAll('[data-key]').forEach(inp => {
                        const key = inp.dataset.key;
                        if (valores[key] === undefined) return;
                        if (inp.dataset.format === 'date') {
                            inp.value = valores[key] ? String(valores[key]).slice(0, 10) : '';
                        } else {
                            inp.value = valores[key] ?? '';
                        }
                    });
                    renderView(row);
                    setModoEdicion(row, false);
                    mostrarAlerta('Cambios guardados.', 'success');
                }).catch(() => {
                    mostrarAlerta('Error de conexión al guardar.', 'danger');
                }).finally(() => {
                    row.querySelectorAll('[data-key]').forEach(inp => inp.disabled = false);
                    if (guardarBtn) guardarBtn.disabled = false;
                });
            }

            function cancelarFila(row) {
                row.querySelectorAll('[data-key]').forEach(inp => {
                    inp.value = inp.dataset.original;
                });
                renderView(row);
                setModoEdicion(row, false);
            }

            const tablaSiguientes = document.getElementById('pagos-siguientes-table');
            if (tablaSiguientes) {
                tablaSiguientes.querySelector('tbody').addEventListener('click', function (e) {
                    const toggle = e.target.closest('.js-inline-toggle');
                    const save = e.target.closest('.js-inline-save');
                    const cancel = e.target.closest('.js-inline-cancel');
                    const row = e.target.closest('tr.ip-inline-row');
                    if (!row) return;

                    if (toggle) {
                        tablaSiguientes.querySelectorAll('tr.is-editing').forEach(r => cancelarFila(r));
                        setModoEdicion(row, true);
                    } else if (save) {
                        save.disabled = true;
                        guardarFila(row);
                    } else if (cancel) {
                        cancelarFila(row);
                    }
                });
            }

            const form = document.getElementById('form-precargar');
            const checkAll = document.getElementById('check-all');
            const resumenes = Array.from(form?.querySelectorAll('.js-resumen-seleccion') ?? []);
            const btnsGuardar = Array.from(form?.querySelectorAll('.js-btn-guardar') ?? []);

            if (form && checkAll && btnsGuardar.length) {
                const checks = Array.from(form.querySelectorAll('.js-row-check'));

                function contarSeleccionadas() {
                    return checks.filter(function (c) { return c.checked; }).length;
                }

                function sincronizar() {
                    const n = contarSeleccionadas();
                    resumenes.forEach(function (r) { r.textContent = n + ' seleccionado(s)'; });
                    btnsGuardar.forEach(function (b) { b.disabled = n === 0; });
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
                    if (!confirm('Se crearán ' + n + ' pagos nuevos. ¿Continuar?')) {
                        e.preventDefault();
                    }
                });

                sincronizar();
            }
        })();
    </script>
@endpush
