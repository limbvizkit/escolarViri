@extends('layouts.app')

@section('title', 'Agregar alumno a taller')

@section('content')
    <div class="row justify-content-center mb-4">
        <div class="col-lg-7">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Agregar alumno a: {{ $taller->nombre }}</h5>
                </div>

                <div class="ip-card-body">
                    <form action="{{ route('talleres.alumnos.store', $taller) }}" method="POST">
                        @csrf

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="alumno_id" class="form-label">Alumno <span class="ip-required">*</span></label>
                                <select id="alumno_id" name="alumno_id"
                                        class="form-select @error('alumno_id') is-invalid @enderror" required>
                                    <option value="">— Seleccionar alumno —</option>
                                    @forelse ($alumnosDisponibles as $alumno)
                                        <option value="{{ $alumno->id }}" {{ old('alumno_id') == $alumno->id ? 'selected' : '' }}>
                                            {{ $alumno->nombre_completo }} — {{ $alumno->gradoEscolar->nombre ?? 'Sin grado escolar' }}
                                        </option>
                                    @empty
                                        <option value="" disabled>Todos los alumnos activos ya están inscritos en este taller.</option>
                                    @endforelse
                                </select>
                                @error('alumno_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label for="hora_inicio" class="form-label">Hora inicio <span class="ip-required">*</span></label>
                                <input type="time" id="hora_inicio" name="hora_inicio"
                                       class="form-control @error('hora_inicio') is-invalid @enderror"
                                       value="{{ old('hora_inicio') }}" required>
                                @error('hora_inicio')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-3">
                                <label for="hora_fin" class="form-label">Hora fin <span class="ip-required">*</span></label>
                                <input type="time" id="hora_fin" name="hora_fin"
                                       class="form-control @error('hora_fin') is-invalid @enderror"
                                       value="{{ old('hora_fin') }}" required>
                                @error('hora_fin')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4">
                                <label for="monto_pagado" class="form-label">Monto pagado</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0" id="monto_pagado" name="monto_pagado"
                                           class="form-control @error('monto_pagado') is-invalid @enderror"
                                           value="{{ old('monto_pagado') }}">
                                </div>
                                @error('monto_pagado')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                <div class="form-text">Opcional; puede ajustarse después en la tabla.</div>
                            </div>
                        </div>

                        <div class="ip-form-actions">
                            <a href="{{ route('talleres.index') }}" class="btn ip-btn-outline">Cancelar</a>
                            <button type="submit" class="btn ip-btn-success">
                                <i class="bi bi-check-lg me-1"></i>Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Inscripción múltiple a: {{ $taller->nombre }}</h5>
                </div>

                <div class="ip-card-body">
                    @if ($alumnosDisponibles->isEmpty())
                        <p class="ip-muted mb-0">Todos los alumnos activos ya están inscritos en este taller.</p>
                    @else
                        <form action="{{ route('talleres.alumnos.bulk.store', $taller) }}" method="POST">
                            @csrf

                            @error('seleccionados')
                                <div class="text-danger small mb-2">{{ $message }}</div>
                            @enderror

                            @if ($errors->has('seleccionados.*'))
                                <div class="text-danger small mb-2">
                                    Uno de los alumnos seleccionados no está disponible para este taller.
                                </div>
                            @endif

                            <p class="ip-muted mb-2">Selecciona los alumnos; los campos se habilitan solo para los marcados.</p>

                            <div class="ip-form-actions ip-form-actions-top">
                                <a href="{{ route('talleres.index') }}" class="btn ip-btn-outline">Cancelar</a>
                                <button type="submit" class="btn ip-btn-success">
                                    <i class="bi bi-check-lg me-1"></i>Guardar seleccionados
                                </button>
                            </div>

                            <div class="ip-table-scroll">
                                <table class="table ip-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center">
                                                <input class="form-check-input" type="checkbox" id="seleccionar-todos" aria-label="Seleccionar todos">
                                            </th>
                                            <th>Alumno</th>
                                            <th>Grado escolar</th>
                                            <th>Hora inicio <span class="ip-required">*</span></th>
                                            <th>Hora fin <span class="ip-required">*</span></th>
                                            <th>Monto pagado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($alumnosDisponibles as $alumno)
                                            @php
                                                $seleccionado = in_array((string) $alumno->id, old('seleccionados', []));
                                            @endphp
                                            <tr>
                                                <td class="text-center">
                                                    <input class="form-check-input alumno-checkbox" type="checkbox"
                                                           name="seleccionados[]" value="{{ $alumno->id }}"
                                                           data-alumno-id="{{ $alumno->id }}"
                                                           @checked($seleccionado)>
                                                </td>
                                                <td>{{ $alumno->nombre_completo }}</td>
                                                <td>{{ $alumno->gradoEscolar->nombre ?? 'Sin grado escolar' }}</td>
                                                <td>
                                                    <input type="time" name="alumnos[{{ $alumno->id }}][hora_inicio]"
                                                           class="form-control form-control-sm bulk-input" data-required="1"
                                                           value="{{ old('alumnos.'.$alumno->id.'.hora_inicio') }}"
                                                           @disabled(!$seleccionado) @required($seleccionado)>
                                                    @error('alumnos.'.$alumno->id.'.hora_inicio')
                                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="time" name="alumnos[{{ $alumno->id }}][hora_fin]"
                                                           class="form-control form-control-sm bulk-input" data-required="1"
                                                           value="{{ old('alumnos.'.$alumno->id.'.hora_fin') }}"
                                                           @disabled(!$seleccionado) @required($seleccionado)>
                                                    @error('alumnos.'.$alumno->id.'.hora_fin')
                                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" placeholder="0.00"
                                                           name="alumnos[{{ $alumno->id }}][monto_pagado]"
                                                           class="form-control form-control-sm bulk-input"
                                                           value="{{ old('alumnos.'.$alumno->id.'.monto_pagado') }}"
                                                           @disabled(!$seleccionado)>
                                                    @error('alumnos.'.$alumno->id.'.monto_pagado')
                                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="ip-form-actions">
                                <a href="{{ route('talleres.index') }}" class="btn ip-btn-outline">Cancelar</a>
                                <button type="submit" class="btn ip-btn-success">
                                    <i class="bi bi-check-lg me-1"></i>Guardar seleccionados
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const selectAll = document.getElementById('seleccionar-todos');
            const checkboxes = document.querySelectorAll('.alumno-checkbox');

            function updateRow(checkbox) {
                const row = checkbox.closest('tr');
                if (!row) {
                    return;
                }

                row.querySelectorAll('.bulk-input').forEach(function (input) {
                    input.disabled = !checkbox.checked;
                    if (input.dataset.required === '1') {
                        input.required = checkbox.checked;
                    }
                });
            }

            checkboxes.forEach(function (checkbox) {
                updateRow(checkbox);

                checkbox.addEventListener('change', function () {
                    updateRow(checkbox);

                    if (selectAll) {
                        selectAll.checked = Array.from(checkboxes).every(function (cb) {
                            return cb.checked;
                        });
                    }
                });
            });

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    checkboxes.forEach(function (checkbox) {
                        checkbox.checked = selectAll.checked;
                        updateRow(checkbox);
                    });
                });
            }
        })();
    </script>
@endpush
