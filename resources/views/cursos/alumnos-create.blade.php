@extends('layouts.app')

@section('title', 'Agregar alumno a curso')

@section('content')
    <div class="row justify-content-center mb-4">
        <div class="col-lg-7">
            <div class="ip-card">
                <div class="ip-card-header">
                    <h5 class="ip-card-title">Agregar alumno a: {{ $curso->nombre }}</h5>
                </div>

                <div class="ip-card-body">
                    <form action="{{ route('cursos.alumnos.store', $curso) }}" method="POST">
                        @csrf

                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label for="alumno_id" class="form-label">Alumno <span class="ip-required">*</span></label>
                                <select id="alumno_id" name="alumno_id"
                                        class="form-select @error('alumno_id') is-invalid @enderror" required>
                                    <option value="">— Seleccionar alumno —</option>
                                    @forelse ($alumnosDisponibles as $alumno)
                                        <option value="{{ $alumno->id }}" {{ old('alumno_id') == $alumno->id ? 'selected' : '' }}>
                                            {{ $alumno->nombre_completo }} — {{ $alumno->gradoEscolar->nombre ?? 'Sin grado escolar' }}
                                        </option>
                                    @empty
                                        <option value="" disabled>Todos los alumnos activos ya están inscritos en este curso.</option>
                                    @endforelse
                                </select>
                                @error('alumno_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="ip-form-actions">
                            <a href="{{ route('cursos.index') }}" class="btn ip-btn-outline">Cancelar</a>
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
                    <h5 class="ip-card-title">Inscripción múltiple a: {{ $curso->nombre }}</h5>
                </div>

                <div class="ip-card-body">
                    @if ($alumnosDisponibles->isEmpty())
                        <p class="ip-muted mb-0">Todos los alumnos activos ya están inscritos en este curso.</p>
                    @else
                        <form action="{{ route('cursos.alumnos.bulk.store', $curso) }}" method="POST">
                            @csrf

                            @error('seleccionados')
                                <div class="text-danger small mb-2">{{ $message }}</div>
                            @enderror

                            @if ($errors->has('seleccionados.*'))
                                <div class="text-danger small mb-2">
                                    Uno de los alumnos seleccionados no está disponible para este curso.
                                </div>
                            @endif

                            <p class="ip-muted mb-2">Selecciona los alumnos que deseas inscribir.</p>

                            <div class="ip-form-actions ip-form-actions-top">
                                <a href="{{ route('cursos.index') }}" class="btn ip-btn-outline">Cancelar</a>
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
                                                           @checked($seleccionado)>
                                                </td>
                                                <td>{{ $alumno->nombre_completo }}</td>
                                                <td>{{ $alumno->gradoEscolar->nombre ?? 'Sin grado escolar' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="ip-form-actions">
                                <a href="{{ route('cursos.index') }}" class="btn ip-btn-outline">Cancelar</a>
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
            'use strict';

            const selectAll = document.getElementById('seleccionar-todos');
            const checkboxes = document.querySelectorAll('.alumno-checkbox');

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    checkboxes.forEach(function (checkbox) {
                        checkbox.checked = selectAll.checked;
                    });
                });
            }

            checkboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    if (selectAll) {
                        selectAll.checked = Array.from(checkboxes).every(function (cb) {
                            return cb.checked;
                        });
                    }
                });
            });
        })();
    </script>
@endpush
