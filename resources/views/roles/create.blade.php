@php
    $editing = isset($rol);
    $title = $editing ? 'Editar rol' : 'Nuevo rol';
    $action = $editing ? route('roles.update', $rol) : route('roles.store');
    $method = $editing ? 'PUT' : 'POST';
    $editable = auth()->user()?->esSuperAdmin() ?? false;
@endphp

@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <form action="{{ $action }}" method="POST">
                @csrf
                @method($method)

                <div class="ip-card">
                    <div class="ip-card-header">
                        <h5 class="ip-card-title">Datos del rol</h5>
                    </div>

                    <div class="ip-card-body">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre <span class="ip-required">*</span></label>
                            <input type="text" id="nombre" name="nombre"
                                   class="form-control @error('nombre') is-invalid @enderror"
                                   value="{{ old('nombre', $rol->nombre ?? '') }}" required>
                            @error('nombre')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-0">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" id="slug" name="slug"
                                   class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug', $rol->slug ?? '') }}"
                                   placeholder="Se genera automáticamente"
                                   @disabled($editing && $rol->esProtegido())>
                            @error('slug')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            @if ($editing && $rol->esProtegido())
                                <div class="form-text">El identificador del rol Super Administrador está protegido y no se puede modificar.</div>
                            @else
                                <div class="form-text">Identificador único en minúsculas y guiones. Vacío = automático.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="ip-card mt-3">
                    <div class="ip-card-header">
                        <h5 class="ip-card-title">Permisos por módulo</h5>
                    </div>

                    @unless ($editable)
                        <div class="ip-card-body py-2">
                            <p class="ip-muted mb-0">
                                <i class="bi bi-lock-fill me-1"></i>Solo los super administradores pueden cambiar los permisos.
                            </p>
                        </div>
                    @endunless

                    <div class="table-responsive">
                        <table class="table ip-table mb-0">
                            <thead>
                                <tr>
                                    <th>Módulo</th>
                                    <th class="text-center">Ver</th>
                                    <th class="text-center">Escribir</th>
                                    <th class="text-center">Eliminar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($modulos as $grupo => $items)
                                    <tr class="table-light">
                                        <td colspan="4" class="fw-semibold">{{ $grupo }}</td>
                                    </tr>
                                    @foreach ($items as $modulo)
                                        @php $permiso = $permisos[$modulo->id] ?? null; @endphp
                                        <tr data-modulo="{{ $modulo->id }}">
                                            <td>{{ $modulo->nombre }}</td>
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input permiso-ver"
                                                       name="permisos[{{ $modulo->id }}][ver]" value="1"
                                                       aria-label="Ver {{ $modulo->nombre }}"
                                                       @checked(old("permisos.{$modulo->id}.ver", $permiso?->puede_ver))
                                                       @disabled(! $editable)>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input permiso-escribir"
                                                       name="permisos[{{ $modulo->id }}][escribir]" value="1"
                                                       aria-label="Escribir {{ $modulo->nombre }}"
                                                       @checked(old("permisos.{$modulo->id}.escribir", $permiso?->puede_escribir))
                                                       @disabled(! $editable)>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input permiso-eliminar"
                                                       name="permisos[{{ $modulo->id }}][eliminar]" value="1"
                                                       aria-label="Eliminar {{ $modulo->nombre }}"
                                                       @checked(old("permisos.{$modulo->id}.eliminar", $permiso?->puede_eliminar))
                                                       @disabled(! $editable)>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="ip-form-actions mt-3">
                    <a href="{{ route('roles.index') }}" class="btn ip-btn-outline">Cancelar</a>
                    <button type="submit" class="btn ip-btn-success">
                        <i class="bi bi-check-lg me-1"></i>{{ $editing ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var editable = @json($editable);

            document.querySelectorAll('tr[data-modulo]').forEach(function (row) {
                var ver = row.querySelector('.permiso-ver');
                var otros = row.querySelectorAll('.permiso-escribir, .permiso-eliminar');

                if (!ver || otros.length === 0) {
                    return;
                }

                function sync() {
                    var locked = Array.prototype.some.call(otros, function (box) {
                        return box.checked;
                    });

                    if (locked) {
                        ver.checked = true;
                    }

                    if (editable) {
                        ver.disabled = locked;
                    }
                }

                Array.prototype.forEach.call(otros, function (box) {
                    box.addEventListener('change', sync);
                });

                sync();
            });
        })();
    </script>
@endpush
