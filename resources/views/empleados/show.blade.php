@extends('layouts.app')

@section('title', $empleado->nombre_completo)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Detalle del empleado</p>
        <div class="d-flex gap-2">
            <a href="{{ route('empleados.edit', $empleado) }}" class="btn ip-btn-outline">Editar</a>
            @if (!$empleado->usuario)
                <a href="{{ route('usuarios.create', ['empleado_id' => $empleado->id]) }}" class="btn ip-btn">
                    <i class="bi bi-person-plus me-1"></i>Crear usuario
                </a>
            @endif
        </div>
    </div>

    <div class="ip-card">
        <div class="ip-card-header">
            <h5 class="ip-card-title">{{ $empleado->nombre_completo }}</h5>
            <span class="badge ip-badge-{{ $empleado->estatus_badge }}">
                {{ $empleado->estatus_es_activo ? 'Activo' : 'Inactivo' }}
            </span>
        </div>
        <div class="ip-card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="ip-detail-label">Sucursal</div>
                    <div class="ip-detail-value">{{ $empleado->sucursal->nombre ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Área / Puesto</div>
                    <div class="ip-detail-value">{{ $empleado->puesto ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Horario</div>
                    <div class="ip-detail-value">{{ $empleado->horario ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Teléfono</div>
                    <div class="ip-detail-value">{{ $empleado->telefono ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Teléfono personal</div>
                    <div class="ip-detail-value">{{ $empleado->telefono_personal ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Correo</div>
                    <div class="ip-detail-value">{{ $empleado->email ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Fecha de nacimiento</div>
                    <div class="ip-detail-value">
                        {{ $empleado->fecha_nacimiento ? $empleado->fecha_nacimiento->format('d/m/Y') : '—' }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Tipo de sangre</div>
                    <div class="ip-detail-value">{{ $empleado->tipo_sangre ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">CURP</div>
                    <div class="ip-detail-value">{{ $empleado->curp ?? '—' }}</div>
                </div>
                <div class="col-12">
                    <div class="ip-detail-label">Dirección</div>
                    <div class="ip-detail-value">{{ $empleado->direccion ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Números de emergencias</div>
                    <div class="ip-detail-value">{{ $empleado->numeros_emergencia ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Enfermedad</div>
                    <div class="ip-detail-value">{{ $empleado->enfermedad ?? '—' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="ip-detail-label">Alergias</div>
                    <div class="ip-detail-value">{{ $empleado->alergias ?? '—' }}</div>
                </div>
                <div class="col-12">
                    <div class="ip-detail-label">Medicamento</div>
                    <div class="ip-detail-value">{{ $empleado->medicamento ?? '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="ip-detail-label">Usuario asociado</div>
                    <div class="ip-detail-value">
                        @if ($empleado->usuario)
                            {{ $empleado->usuario->name }} ({{ $empleado->usuario->email }})
                        @else
                            <span class="ip-muted">Sin usuario asignado</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
