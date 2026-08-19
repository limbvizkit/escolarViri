@extends('layouts.app')

@section('title', 'Alumnos')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Gestión de alumnos por grado escolar</p>
        <div class="d-flex gap-2">
            @php
                $exportQuery = array_filter(request()->only(['q', 'grado_escolar_id', 'estatus', 'sort', 'direction']), fn ($v) => $v !== null && $v !== '');
            @endphp
            <a href="{{ route('alumnos.export.pdf', $exportQuery) }}" class="btn ip-btn-danger btn-sm">
                <i class="bi bi-file-earmark-pdf me-1"></i>PDF
            </a>
            <a href="{{ route('alumnos.export.excel', $exportQuery) }}" class="btn ip-btn-success btn-sm">
                <i class="bi bi-file-earmark-excel me-1"></i>Excel
            </a>
            <a href="{{ route('alumnos.create') }}" class="btn ip-btn">
                <i class="bi bi-plus-lg me-1"></i>Nuevo alumno
            </a>
        </div>
    </div>

    @include('partials.table-filters', [
        'filters' => $filtros,
        'placeholder' => 'Buscar por nombre, apellidos, horario...',
    ])

    <div class="ip-card">
        <div class="ip-card-header">
            <span class="ip-table-summary">Mostrando {{ $alumnos->currentPage() }} de {{ $alumnos->lastPage() }}</span>
            <h5 class="ip-card-title">Listado de alumnos</h5>
        </div>

        <div class="ip-card-body ip-table-hint">
            <i class="bi bi-pencil-fill me-1"></i>Haz clic en el lápiz de una fila para editar en la tabla las columnas que quieras.
        </div>

        <div class="table-responsive">
            <table class="table ip-table mb-0" id="alumnos-table">
                <thead>
                    <tr>
                        <x-sortable field="id" label="#" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="nombre" label="Nombre" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="apellido_paterno" label="Apellido paterno" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="apellido_materno" label="Apellido materno" :current="request('sort')" :direction="request('direction')" />
                        <th>Grado Escolar</th>
                        <x-sortable field="fecha_nacimiento" label="Fecha nacimiento" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="horario" label="Horario" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="inscripcion" label="Inscripción" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="reinscripcion" label="Re/Inscripción" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="entrevista_inicial" label="Entrevista" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="nat_geo" label="Nat Geo" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="cuota_materiales" label="Cuota materiales" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="fecha_ingreso" label="Fecha ingreso" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="cuota_mensual" label="Cuota mensual" :current="request('sort')" :direction="request('direction')" />
                        <x-sortable field="estatus" label="Estatus" :current="request('sort')" :direction="request('direction')" />
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($alumnos as $alumno)
                        @php
                            $formId = 'inline-' . $alumno->id;
                            $montos = [
                                'inscripcion' => $alumno->inscripcion,
                                'reinscripcion' => $alumno->reinscripcion,
                                'entrevista_inicial' => $alumno->entrevista_inicial,
                                'nat_geo' => $alumno->nat_geo,
                                'cuota_materiales' => $alumno->cuota_materiales,
                            ];
                        @endphp
                        <tr class="ip-inline-row" data-row="{{ $alumno->id }}">
                            <td>{{ $alumno->id }}</td>

                            <td>
                                <div class="cell-view fw-semibold" data-target="nombre">{{ $alumno->nombre }}</div>
                                <input type="text" name="nombre" form="{{ $formId }}" data-key="nombre" data-format="text"
                                       class="form-control form-control-sm cell-edit d-none"
                                       value="{{ $alumno->nombre }}" data-original="{{ $alumno->nombre }}">
                            </td>

                            <td>
                                <div class="cell-view" data-target="apellido_paterno">{{ $alumno->apellido_paterno }}</div>
                                <input type="text" name="apellido_paterno" form="{{ $formId }}" data-key="apellido_paterno" data-format="text"
                                       class="form-control form-control-sm cell-edit d-none"
                                       value="{{ $alumno->apellido_paterno }}" data-original="{{ $alumno->apellido_paterno }}">
                            </td>

                            <td>
                                <div class="cell-view" data-target="apellido_materno">{{ $alumno->apellido_materno ?? '—' }}</div>
                                <input type="text" name="apellido_materno" form="{{ $formId }}" data-key="apellido_materno" data-format="text"
                                       class="form-control form-control-sm cell-edit d-none"
                                       value="{{ $alumno->apellido_materno }}" data-original="{{ $alumno->apellido_materno }}">
                            </td>

                            <td>
                                <div class="cell-view">
                                    <span class="badge" data-target="grado_escolar_id" style="background:#eaf1ff;color:var(--ip-primary);font-weight:600;">
                                        {{ $alumno->gradoEscolar->nombre ?? '—' }}
                                    </span>
                                </div>
                                <select name="grado_escolar_id" form="{{ $formId }}" data-key="grado_escolar_id" data-format="grado_escolar"
                                        class="form-select form-select-sm cell-edit d-none"
                                        data-original="{{ $alumno->grado_escolar_id }}">
                                    <option value="">— Sin grado escolar —</option>
                                    @foreach ($gradosEscolares as $gradoEscolar)
                                        <option value="{{ $gradoEscolar->id }}" {{ $alumno->grado_escolar_id == $gradoEscolar->id ? 'selected' : '' }}>
                                            {{ $gradoEscolar->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>

                            <td>
                                <div class="cell-view" data-target="fecha_nacimiento">
                                    {{ $alumno->fecha_nacimiento?->format('d/m/Y') ?? '—' }}
                                </div>
                                <input type="date" name="fecha_nacimiento" form="{{ $formId }}" data-key="fecha_nacimiento" data-format="date"
                                       class="form-control form-control-sm cell-edit d-none"
                                       value="{{ $alumno->fecha_nacimiento?->format('Y-m-d') }}"
                                       data-original="{{ $alumno->fecha_nacimiento?->format('Y-m-d') }}">
                            </td>

                            <td>
                                <div class="cell-view" data-target="horario">{{ $alumno->horario ?? '—' }}</div>
                                <input type="text" name="horario" form="{{ $formId }}" data-key="horario" data-format="text"
                                       class="form-control form-control-sm cell-edit d-none"
                                       value="{{ $alumno->horario }}" data-original="{{ $alumno->horario }}">
                            </td>

                            @foreach ($montos as $campo => $monto)
                                <td>
                                    <div class="cell-view" data-target="{{ $campo }}">
                                        {{ $monto ? '$' . number_format((float) $monto, 2) : 'NA' }}
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
                                <div class="cell-view" data-target="fecha_ingreso">
                                    {{ $alumno->fecha_ingreso?->format('d/m/Y') ?? '—' }}
                                </div>
                                <input type="date" name="fecha_ingreso" form="{{ $formId }}" data-key="fecha_ingreso" data-format="date"
                                       class="form-control form-control-sm cell-edit d-none"
                                       value="{{ $alumno->fecha_ingreso?->format('Y-m-d') }}"
                                       data-original="{{ $alumno->fecha_ingreso?->format('Y-m-d') }}">
                            </td>

                            <td>
                                <div class="cell-view" data-target="cuota_mensual">
                                    {{ $alumno->cuota_mensual ? '$' . number_format((float) $alumno->cuota_mensual, 2) : 'NA' }}
                                </div>
                                <div class="input-group input-group-sm cell-edit d-none">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0" name="cuota_mensual" form="{{ $formId }}"
                                           data-key="cuota_mensual" data-format="money"
                                           class="form-control" value="{{ $alumno->cuota_mensual }}" data-original="{{ $alumno->cuota_mensual }}">
                                </div>
                            </td>

                            <td>
                                <div class="cell-view">
                                    <span class="badge ip-badge-{{ $alumno->estatus ? 'active' : 'inactive' }}" data-target="estatus">
                                        {{ $alumno->estatus ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>
                                <select name="estatus" form="{{ $formId }}" data-key="estatus" data-format="estatus"
                                        class="form-select form-select-sm cell-edit d-none"
                                        data-original="{{ $alumno->estatus ? '1' : '0' }}">
                                    <option value="1" {{ $alumno->estatus ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ !$alumno->estatus ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </td>

                            <td class="text-end">
                                <form id="{{ $formId }}" method="POST" action="{{ route('alumnos.inline-update', $alumno) }}">
                                    @csrf
                                    @method('PUT')
                                </form>
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('alumnos.show', $alumno) }}" class="ip-action" title="Ver">
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
                                    <a href="{{ route('alumnos.edit', $alumno) }}" class="ip-action" title="Editar (página)">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('alumnos.destroy', $alumno) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar este alumno?')">
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
                            <td colspan="16" class="text-center ip-muted py-4">
                                No hay alumnos registrados.
                                <a href="{{ route('alumnos.create') }}" class="d-block mt-2">Crear el primero</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ip-card-body d-flex justify-content-center">
            {{ $alumnos->links() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';

            const table = document.getElementById('alumnos-table');
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
                row.querySelectorAll('.cell-edit').forEach(function (inp) {
                    const key = inp.dataset.key;
                    const view = row.querySelector('[data-target="' + key + '"]');
                    if (!view) return;
                    const fmt = inp.dataset.format;
                    const val = inp.value;
                    if (fmt === 'date') {
                        view.textContent = formatearFecha(val);
                    } else if (fmt === 'money') {
                        view.textContent = val === '' || val === null ? 'NA' : '$' + Number(val).toFixed(2);
                    } else if (fmt === 'grado_escolar') {
                        view.textContent = inp.options[inp.selectedIndex].text;
                    } else if (fmt === 'estatus') {
                        const activo = val === '1';
                        view.textContent = activo ? 'Activo' : 'Inactivo';
                        view.classList.toggle('ip-badge-active', activo);
                        view.classList.toggle('ip-badge-inactive', !activo);
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