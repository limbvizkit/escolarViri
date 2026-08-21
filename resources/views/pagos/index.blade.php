@extends('layouts.app')

@section('title', 'Pagos')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Pagos mensuales por alumno</p>
        <div class="d-flex gap-2">
            @php
                $exportQuery = array_filter(request()->only(['q', 'mes', 'forma_pago_id', 'sort', 'direction']), fn ($v) => $v !== null && $v !== '');
            @endphp
            <a href="{{ route('pagos.export.pdf', $exportQuery) }}" class="btn ip-btn-danger btn-sm">
                <i class="bi bi-file-earmark-pdf me-1"></i>PDF
            </a>
            <a href="{{ route('pagos.export.excel', $exportQuery) }}" class="btn ip-btn-success btn-sm">
                <i class="bi bi-file-earmark-excel me-1"></i>Excel
            </a>
            <a href="{{ route('pagos.precargar') }}" class="btn ip-btn-outline">
                <i class="bi bi-collection me-1"></i>Precargar pagos
            </a>
            <a href="{{ route('pagos.create') }}" class="btn ip-btn">
                <i class="bi bi-plus-lg me-1"></i>Nuevo pago
            </a>
        </div>
    </div>

    @include('partials.table-filters', [
        'filters' => $filtros,
        'placeholder' => 'Buscar por nombre del alumno...',
    ])

    <div class="ip-card">
        <div class="ip-card-header">
            <span class="ip-table-summary">Mostrando {{ $pagos->currentPage() }} de {{ $pagos->lastPage() }}</span>
            <h5 class="ip-card-title">Listado de pagos</h5>
        </div>

        <div class="ip-card-body ip-table-hint">
            <i class="bi bi-pencil-fill me-1"></i>Haz clic en el lápiz de una fila para editar en la tabla las columnas que quieras.
        </div>

        <div class="table-responsive">
            <table class="table ip-table mb-0" id="pagos-table">
                <thead>
                    <tr>
                        <x-sortable field="id" label="#" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="alumno_id" label="Alumno" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="mes" label="Mes" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="fecha" label="Fecha" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="entrada_8am" label="Entrada 8AM" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="pronto_pago" label="Pronto pago" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="pago_normal" label="Pago normal" :current="request('sort')" :direction="request('direction')" />
                        <th>Forma de pago</th>
                        <x-sortable field="talleres" label="Talleres" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="lunch" label="Lunch" :current="request('sort')" :direction="request('direction')" />
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pagos as $pago)
                        @php
                            $formId = 'inline-' . $pago->id;
                            $montos = [
                                'entrada_8am' => $pago->entrada_8am,
                                'pronto_pago' => $pago->pronto_pago,
                                'pago_normal' => $pago->pago_normal,
                            ];
                        @endphp
                        <tr class="ip-inline-row" data-row="{{ $pago->id }}">
                            <td>{{ $pago->id }}</td>

                            <td>
                                <div class="cell-view" data-target="alumno_id">
                                    <a href="{{ route('alumnos.show', $pago->alumno) }}" class="fw-semibold ip-link">{{ $pago->alumno->nombre_completo }}</a>
                                    <span class="badge ms-1" style="background:#eaf1ff;color:var(--ip-primary);font-weight:600;">{{ $pago->alumno->gradoEscolar->nombre ?? '—' }}</span>
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
                                <div class="cell-view" data-target="forma_pago_id">
                                    <span class="badge" style="background:#eaf1ff;color:var(--ip-primary);font-weight:600;">
                                        {{ $pago->formaPago->nombre ?? '—' }}
                                    </span>
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

                            <td class="text-end">
                                <form id="{{ $formId }}" method="POST" action="{{ route('pagos.inline-update', $pago) }}">
                                    @csrf
                                    @method('PUT')
                                </form>
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('pagos.show', $pago) }}" class="ip-action" title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>
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
                                    <form action="{{ route('pagos.destroy', $pago) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar este pago?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ip-action ip-action-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center ip-muted py-4">
                                No hay pagos registrados.
                                <a href="{{ route('pagos.create') }}" class="d-block mt-2">Registrar el primero</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ip-card-body d-flex justify-content-center">
            {{ $pagos->links() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';

            const table = document.getElementById('pagos-table');
            if (!table) return;

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

            function mostrarAlerta(mensaje, tipo) {
                const cont = document.getElementById('ip-inline-alerts');
                if (!cont) return;
                const a = document.createElement('div');
                a.className = 'alert alert-' + (tipo || 'danger') + ' ip-alert alert-dismissible fade show';
                a.setAttribute('role', 'alert');
                a.innerHTML = '<i class="bi bi-' + (tipo === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill') + ' me-1"></i>' + mensaje +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>';
                cont.prepend(a);
                setTimeout(() => a.remove(), 6000);
            }

            function crearContenedorAlertas() {
                let cont = document.getElementById('ip-inline-alerts');
                if (!cont) {
                    cont = document.createElement('div');
                    cont.id = 'ip-inline-alerts';
                    cont.className = 'mb-3';
                    const accion = document.querySelector('main .ip-main, main');
                    (accion || document.body).insertBefore(cont, (accion || document.body).firstChild);
                }
                return cont;
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
                row.querySelectorAll('.cell-view').forEach(c => c.classList.toggle('d-none', editando));
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
                        if (inp.dataset.format === 'estatus') {
                            inp.value = valores[key] ? '1' : '0';
                        } else if (inp.dataset.format === 'date') {
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

            crearContenedorAlertas();

            table.querySelector('tbody').addEventListener('click', function (e) {
                const toggle = e.target.closest('.js-inline-toggle');
                const save = e.target.closest('.js-inline-save');
                const cancel = e.target.closest('.js-inline-cancel');
                const row = e.target.closest('tr.ip-inline-row');
                if (!row) return;

                if (toggle) {
                    row.querySelectorAll('tr.is-editing').forEach(r => cancelarFila(r));
                    setModoEdicion(row, true);
                } else if (save) {
                    save.disabled = true;
                    guardarFila(row);
                } else if (cancel) {
                    cancelarFila(row);
                }
            });
        })();
    </script>
@endpush