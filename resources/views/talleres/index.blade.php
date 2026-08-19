@extends('layouts.app')

@section('title', 'Talleres')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="ip-heading mb-0">Talleres</h2>
        <a href="{{ route('talleres.create') }}" class="btn ip-btn">
            <i class="bi bi-plus-lg me-1"></i>Nuevo taller
        </a>
    </div>

    @forelse ($talleres as $taller)
        @php
            $inscripcionesTaller = $inscripciones->where('taller_id', $taller->id);
        @endphp
        <div class="ip-card mb-4">
            <div class="ip-card-header d-flex flex-wrap align-items-center gap-2">
                <h5 class="ip-card-title mb-0 me-auto">{{ $taller->nombre }}</h5>
                <span class="badge" style="background:#eaf1ff;color:var(--ip-primary);font-weight:600;">
                    ${{ number_format((float) $taller->costo, 2) }}
                </span>
                <div class="d-inline-flex gap-2">
                    <a href="{{ route('talleres.alumnos.create', $taller) }}" class="btn ip-btn btn-sm">
                        <i class="bi bi-person-plus me-1"></i>Agregar alumno
                    </a>
                    <a href="{{ route('talleres.edit', $taller) }}" class="btn ip-btn-outline btn-sm">
                        <i class="bi bi-pencil-square me-1"></i>Editar
                    </a>
                    <form action="{{ route('talleres.destroy', $taller) }}" method="POST"
                          onsubmit="return confirm('¿Seguro que deseas eliminar este taller y sus inscripciones?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn ip-btn-danger btn-sm">
                            <i class="bi bi-trash me-1"></i>Eliminar
                        </button>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table ip-table mb-0 js-talleres-table" id="talleres-table-{{ $taller->id }}">
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Grado Escolar</th>
                            <th>Horario</th>
                            <th>Costo</th>
                            <th>Monto pagado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($inscripcionesTaller as $ins)
                            @php
                                $formId = 'inline-' . $ins->id;
                                $horario = substr($ins->hora_inicio ?? '', 0, 5) . ' - ' . substr($ins->hora_fin ?? '', 0, 5);
                            @endphp
                            <tr class="ip-inline-row" data-row="{{ $ins->id }}">
                                <td class="fw-semibold">{{ $ins->alumno->nombre_completo }}</td>
                                <td>{{ $ins->alumno->gradoEscolar->nombre ?? '—' }}</td>
                                <td>{{ $horario }}</td>
                                <td>${{ number_format((float) $ins->taller->costo, 2) }}</td>
                                <td>
                                    <div class="cell-view" data-target="monto_pagado">
                                        {{ $ins->monto_pagado !== null ? '$' . number_format((float) $ins->monto_pagado, 2) : 'NA' }}
                                    </div>
                                    <div class="input-group input-group-sm cell-edit d-none">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" min="0" name="monto_pagado" form="{{ $formId }}"
                                               data-key="monto_pagado" data-format="money"
                                               class="form-control" value="{{ $ins->monto_pagado }}" data-original="{{ $ins->monto_pagado }}">
                                    </div>
                                </td>
                                <td class="text-end">
                                    <form id="{{ $formId }}" method="POST" action="{{ route('talleres.inscripcion.monto.update', $ins) }}">
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
                                        <form action="{{ route('talleres.alumnos.destroy', [$taller, $ins->alumno]) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('¿Seguro que deseas quitar este alumno del taller?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ip-action ip-action-danger" title="Quitar del taller">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center ip-muted py-4">
                                    Sin alumnos aún. Agregá el primero.
                                    <a href="{{ route('talleres.alumnos.create', $taller) }}" class="d-block mt-2">Agregar alumno</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="ip-card">
            <div class="ip-card-body text-center py-5">
                <i class="bi bi-easel fs-1 d-block mb-3 text-secondary"></i>
                <p class="ip-muted mb-3">Todavía no hay talleres registrados.</p>
                <a href="{{ route('talleres.create') }}" class="btn ip-btn">
                    <i class="bi bi-plus-lg me-1"></i>Crear el primero
                </a>
            </div>
        </div>
    @endforelse
@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';

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
                row.querySelectorAll('.cell-edit').forEach(function (inp) {
                    const key = inp.dataset.key;
                    const view = row.querySelector('[data-target="' + key + '"]');
                    if (!view) return;
                    const val = inp.value;
                    view.textContent = val === '' || val === null ? 'NA' : '$' + Number(val).toFixed(2);
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
                    row.querySelectorAll('.cell-edit').forEach(i => i.disabled = false);
                    const primero = row.querySelector('.cell-edit');
                    if (primero) primero.focus();
                }
            }

            function guardarFila(row) {
                const form = row.querySelector('form[id^="inline-"]');
                if (!form) return;
                row.querySelectorAll('.cell-edit').forEach(inp => {
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
                    row.querySelectorAll('.cell-edit').forEach(inp => {
                        inp.dataset.original = inp.value;
                    });
                    if (datos.valor !== undefined) {
                        const inp = row.querySelector('[data-key="monto_pagado"]');
                        if (inp) inp.value = datos.valor ?? '';
                    }
                    renderView(row);
                    setModoEdicion(row, false);
                    mostrarAlerta('Cambios guardados.', 'success');
                }).catch(() => {
                    mostrarAlerta('Error de conexión al guardar.', 'danger');
                }).finally(() => {
                    row.querySelectorAll('.cell-edit').forEach(inp => inp.disabled = false);
                    if (guardarBtn) guardarBtn.disabled = false;
                });
            }

            function cancelarFila(row) {
                row.querySelectorAll('.cell-edit').forEach(inp => {
                    inp.value = inp.dataset.original;
                });
                renderView(row);
                setModoEdicion(row, false);
            }

            crearContenedorAlertas();

            document.querySelectorAll('.js-talleres-table').forEach(function (table) {
                table.querySelector('tbody').addEventListener('click', function (e) {
                    const toggle = e.target.closest('.js-inline-toggle');
                    const save = e.target.closest('.js-inline-save');
                    const cancel = e.target.closest('.js-inline-cancel');
                    const row = e.target.closest('tr.ip-inline-row');
                    if (!row) return;

                    if (toggle) {
                        setModoEdicion(row, true);
                    } else if (save) {
                        save.disabled = true;
                        guardarFila(row);
                    } else if (cancel) {
                        cancelarFila(row);
                    }
                });
            });
        })();
    </script>
@endpush