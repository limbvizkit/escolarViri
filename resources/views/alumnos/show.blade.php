@extends('layouts.app')

@section('title', $alumno->nombre_completo)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Detalle del alumno</p>
        <a href="{{ route('alumnos.edit', $alumno) }}" class="btn ip-btn">
            <i class="bi bi-pencil-square me-1"></i>Editar
        </a>
    </div>

    <div class="ip-card mb-4">
        <div class="ip-card-header">
            <h5 class="ip-card-title">{{ $alumno->nombre_completo }}</h5>
            <div class="d-flex align-items-center gap-2">
                @if ($alumno->gradoEscolar)
                    <span class="badge" style="background:#eaf1ff;color:var(--ip-primary);font-weight:600;">
                        {{ $alumno->gradoEscolar->nombre }}
                    </span>
                @endif
                <span class="badge ip-badge-{{ $alumno->estatus_badge }}">
                    {{ $alumno->estatus_es_activo ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
        </div>

        <div class="ip-card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="ip-detail-label">Nombre(s)</div>
                    <div class="ip-detail-value">{{ $alumno->nombre }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Apellido paterno</div>
                    <div class="ip-detail-value">{{ $alumno->apellido_paterno }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Apellido materno</div>
                    <div class="ip-detail-value">{{ $alumno->apellido_materno ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Grado Escolar</div>
                    <div class="ip-detail-value">{{ $alumno->gradoEscolar->nombre ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Sucursal</div>
                    <div class="ip-detail-value">{{ $alumno->sucursal->nombre ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Horario</div>
                    <div class="ip-detail-value">{{ $alumno->horario ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Fecha de nacimiento</div>
                    <div class="ip-detail-value">{{ $alumno->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Fecha de ingreso</div>
                    <div class="ip-detail-value">{{ $alumno->fecha_ingreso?->format('d/m/Y') ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Inscripción</div>
                    <div class="ip-detail-value">{{ $alumno->inscripcion ? '$' . number_format($alumno->inscripcion, 2) : 'NA' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Re/Inscripción</div>
                    <div class="ip-detail-value">{{ $alumno->reinscripcion ? '$' . number_format($alumno->reinscripcion, 2) : 'NA' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Entrevista inicial</div>
                    <div class="ip-detail-value">{{ $alumno->entrevista_inicial ? '$' . number_format($alumno->entrevista_inicial, 2) : 'NA' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Nat Geo</div>
                    <div class="ip-detail-value">{{ $alumno->nat_geo ? '$' . number_format($alumno->nat_geo, 2) : 'NA' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Cuota de materiales</div>
                    <div class="ip-detail-value">{{ $alumno->cuota_materiales ? '$' . number_format($alumno->cuota_materiales, 2) : 'NA' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Cuota mensual</div>
                    <div class="ip-detail-value">{{ $alumno->cuota_mensual ? '$' . number_format($alumno->cuota_mensual, 2) : 'NA' }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection