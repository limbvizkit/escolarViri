@extends('layouts.app')

@section('title', 'Adeudo · ' . $adeudo->concepto)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">{{ $adeudo->alumno->nombre_completo }}</p>
        <a href="{{ route('adeudos.index') }}" class="btn ip-btn-outline">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="ip-card mb-4">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Detalle del adeudo</h5>
                </div>
                <div class="ip-card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="ip-detail-label">ALUMNO</div>
                            <div class="ip-detail-value">{{ $adeudo->alumno->nombre_completo }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="ip-detail-label">CONCEPTO</div>
                            <div class="ip-detail-value">{{ $adeudo->concepto }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="ip-detail-label">ANOTACIONES</div>
                            <div class="ip-detail-value">{{ $adeudo->anotaciones ?? '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="ip-detail-label">MONTO</div>
                            <div class="ip-detail-value">{{ '$' . number_format((float) $adeudo->monto, 2) }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="ip-detail-label">ABONADO</div>
                            <div class="ip-detail-value">{{ '$' . number_format((float) $adeudo->monto_pagado, 2) }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="ip-detail-label">PENDIENTE</div>
                            <div class="ip-detail-value">
                                @if ($adeudo->pendiente > 0)
                                    <span class="fw-semibold text-danger">{{ $adeudo->pendienteFormateado }}</span>
                                @else
                                    <span class="fw-semibold text-success">{{ $adeudo->pendienteFormateado }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="ip-detail-label">ESTATUS</div>
                            <div class="ip-detail-value">
                                @php
                                    $badge = match ($adeudo->estatus) {
                                        \App\Models\Adeudo::ESTATUS_PAGADO => 'badge ip-badge-active',
                                        \App\Models\Adeudo::ESTATUS_PARCIAL => 'badge text-bg-info',
                                        default => 'badge text-bg-warning',
                                    };
                                @endphp
                                <span class="{{ $badge }}">{{ ucfirst($adeudo->estatus) }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ip-detail-label">FECHA DE CREACIÓN</div>
                            <div class="ip-detail-value">{{ $adeudo->created_at?->format('d/m/Y') ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Histórico de abonos</h5>
                </div>
                <div class="ip-card-body ip-table-hint">
                    <i class="bi bi-pencil-fill me-1"></i>Haz clic en el lápiz de una fila para editar en la tabla la fecha, forma de pago o monto del abono.
                </div>
                <div class="table-responsive">
                    <table class="table ip-table mb-0" id="abonos-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Forma de pago</th>
                                <th class="text-end">Monto</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($adeudo->abonos as $abono)
                                @php
                                    $formId = 'abono-inline-' . $abono->id;
                                @endphp
                                <tr class="ip-inline-row" data-row="{{ $abono->id }}">
                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        <div class="cell-view" data-target="fecha">{{ $abono->fecha?->format('d/m/Y') ?? '—' }}</div>
                                        <input type="date" name="fecha" form="{{ $formId }}" data-key="fecha" data-format="date"
                                               class="form-control form-control-sm cell-edit d-none"
                                               value="{{ $abono->fecha?->format('Y-m-d') }}"
                                               data-original="{{ $abono->fecha?->format('Y-m-d') }}">
                                    </td>

                                    <td>
                                        <div class="cell-view" data-target="forma_pago_id">
                                            <span class="badge" style="background:#eaf1ff;color:var(--ip-primary);font-weight:600;">
                                                {{ $abono->formaPago->nombre ?? '—' }}
                                            </span>
                                        </div>
                                        <select name="forma_pago_id" form="{{ $formId }}" data-key="forma_pago_id" data-format="forma"
                                                class="form-select form-select-sm cell-edit d-none"
                                                data-original="{{ $abono->forma_pago_id }}">
                                            <option value="">— Sin forma —</option>
                                            @foreach ($formasPago as $forma)
                                                <option value="{{ $forma->id }}" {{ $abono->forma_pago_id == $forma->id ? 'selected' : '' }}>
                                                    {{ $forma->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td class="text-end">
                                        <div class="cell-view" data-target="monto">{{ '$' . number_format((float) $abono->monto, 2) }}</div>
                                        <div class="input-group input-group-sm cell-edit d-none justify-content-end">
                                            <span class="input-group-text">$</span>
                                            <input type="number" step="0.01" min="0.01" name="monto" form="{{ $formId }}"
                                                   data-key="monto" data-format="money"
                                                   class="form-control" value="{{ $abono->monto }}" data-original="{{ $abono->monto }}">
                                        </div>
                                    </td>

                                    <td class="text-end">
                                        <form id="{{ $formId }}" method="POST"
                                              action="{{ route('adeudos.abonos.update', [$adeudo, $abono]) }}">
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
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center ip-muted py-4">Sin abonos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            @if ($adeudo->estatus === \App\Models\Adeudo::ESTATUS_PAGADO)
                <div class="ip-card">
                    <div class="ip-card-header">
                        <h5 class="ip-card-title">Registrar abono</h5>
                    </div>
                    <div class="ip-card-body">
                        <div class="alert alert-success ip-alert mb-0" role="alert">
                            <i class="bi bi-check-circle-fill me-1"></i>Este adeudo ya está liquidado.
                        </div>
                    </div>
                </div>
            @else
                <div class="ip-card">
                    <div class="ip-card-header">
                        <h5 class="ip-card-title">Registrar abono</h5>
                    </div>
                    <div class="ip-card-body">
                        <form action="{{ route('adeudos.abonar', $adeudo) }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="monto" class="form-label">Monto del abono <span class="ip-required">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0.01" id="monto" name="monto"
                                           class="form-control @error('monto') is-invalid @enderror"
                                           value="{{ old('monto') }}" required>
                                </div>
                                @error('monto')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                <div class="form-text">Pendiente actual: {{ $adeudo->pendienteFormateado }}</div>
                            </div>

                            <div class="mb-3">
                                <label for="forma_pago_id" class="form-label">Forma de pago</label>
                                <select id="forma_pago_id" name="forma_pago_id"
                                        class="form-select @error('forma_pago_id') is-invalid @enderror">
                                    <option value="">— Sin forma —</option>
                                    @foreach ($formasPago as $forma)
                                        <option value="{{ $forma->id }}"
                                            {{ old('forma_pago_id') == $forma->id ? 'selected' : '' }}>
                                            {{ $forma->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('forma_pago_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="fecha" class="form-label">Fecha</label>
                                <input type="date" id="fecha" name="fecha"
                                       class="form-control @error('fecha') is-invalid @enderror"
                                       value="{{ old('fecha', now()->format('Y-m-d')) }}">
                                @error('fecha')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="ip-form-actions">
                                <button type="submit" class="btn ip-btn-success">
                                    <i class="bi bi-plus-lg me-1"></i>Registrar abono
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';

            const table = document.getElementById('abonos-table');
            if (!table) return;

            function formatearFecha(v) {
                if (!v) return '—';
                const partes = v.split('-');
                return partes.length === 3 ? partes[2] + '/' + partes[1] + '/' + partes[0] : v;
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
                const form = row.querySelector('form[id^="abono-inline-"]');
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
                    mostrarAlerta('Abono actualizado correctamente.', 'success');
                    setTimeout(() => window.location.reload(), 900);
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