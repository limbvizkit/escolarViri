@extends('layouts.app')

@section('title', 'Documentación Académica')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Documentos académicos por alumno</p>
    </div>

    @include('partials.table-filters', [
        'filters' => [],
        'placeholder' => 'Buscar por nombre, apellidos, horario...',
    ])

    <div class="ip-card">
        <div class="ip-card-header">
            <span class="ip-table-summary">Mostrando {{ $alumnos->currentPage() }} de {{ $alumnos->lastPage() }}</span>
            <h5 class="ip-card-title">Listado de alumnos</h5>
        </div>

        <div class="table-responsive">
            <table class="table ip-table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Alumno</th>
                        <th>Grado</th>
                        <th class="text-center">Documentos</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($alumnos as $alumno)
                        <tr>
                            <td>{{ $alumno->id }}</td>
                            <td class="fw-semibold">{{ $alumno->nombre_completo }}</td>
                            <td>{{ $alumno->gradoEscolar->nombre ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge ip-badge-active">{{ $alumno->academic_documents_count }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('academic-documents.show', $alumno) }}" class="btn ip-btn-outline btn-sm">
                                    <i class="bi bi-folder2-open me-1"></i>Documentos académicos
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center ip-muted py-4">
                                No hay alumnos registrados.
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
