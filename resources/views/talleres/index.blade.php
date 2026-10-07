@extends('layouts.app')

@section('title', 'Talleres')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="ip-heading mb-0">Talleres</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('talleres.export.pdf') }}" class="btn ip-btn-danger btn-sm">
                <i class="bi bi-file-earmark-pdf me-1"></i>PDF
            </a>
            <a href="{{ route('talleres.export.excel') }}" class="btn ip-btn-success btn-sm">
                <i class="bi bi-file-earmark-excel me-1"></i>Excel
            </a>
            <a href="{{ route('talleres.create') }}" class="btn ip-btn">
                <i class="bi bi-plus-lg me-1"></i>Nuevo taller
            </a>
        </div>
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
                <table class="table ip-table mb-0">
                    <thead>
                        <tr>
                            <th>Alumno</th>
                            <th>Grado Escolar</th>
                            <th>Horario</th>
                            <th>Costo</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($inscripcionesTaller as $ins)
                            @php
                                $horario = substr($ins->hora_inicio ?? '', 0, 5) . ' - ' . substr($ins->hora_fin ?? '', 0, 5);
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $ins->alumno->nombre_completo }}</td>
                                <td>{{ $ins->alumno->gradoEscolar->nombre ?? '—' }}</td>
                                <td>{{ $horario }}</td>
                                <td>${{ number_format((float) $ins->taller->costo, 2) }}</td>
                                <td class="text-end">
                                    <form action="{{ route('talleres.alumnos.destroy', [$taller, $ins->alumno]) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Seguro que deseas quitar este alumno del taller?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ip-action ip-action-danger" title="Quitar del taller">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center ip-muted py-4">
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
