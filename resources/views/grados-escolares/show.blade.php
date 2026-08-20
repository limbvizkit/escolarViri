@extends('layouts.app')

@section('title', $gradoEscolar->nombre)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Detalle del grado escolar</p>
        <a href="{{ route('alumnos.create', ['grado_escolar_id' => $gradoEscolar->id]) }}" class="btn ip-btn">
            <i class="bi bi-person-plus me-1"></i>Nuevo alumno
        </a>
    </div>

    <div class="ip-card mb-4">
        <div class="ip-card-header">
            <h5 class="ip-card-title">{{ $gradoEscolar->nombre }}</h5>
            <span class="badge ip-badge-active">{{ $gradoEscolar->alumnos->count() }} alumnos</span>
        </div>
    </div>

    <div class="ip-card">
        <div class="table-responsive">
            <table class="table ip-table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre completo</th>
                        <th>Horario</th>
                        <th>Cuota mensual</th>
                        <th>Estatus</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($gradoEscolar->alumnos as $alumno)
                        <tr>
                            <td>{{ $alumno->id }}</td>
                            <td class="fw-semibold">{{ $alumno->nombre_completo }}</td>
                            <td>{{ $alumno->horario ?? '—' }}</td>
                            <td>{{ $alumno->cuota_mensual ? '$' . number_format($alumno->cuota_mensual, 2) : '—' }}</td>
                            <td>
                                <span class="badge ip-badge-{{ $alumno->estatus_badge }}">
                                    {{ $alumno->estatus_es_activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center ip-muted py-4">Sin alumnos en este grado escolar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection