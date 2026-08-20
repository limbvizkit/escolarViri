@extends('layouts.app')

@section('title', 'Documentación')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="ip-muted mb-0">Estado de documentos por alumno</p>
    </div>

    @include('partials.table-filters', [
        'filters' => [],
        'placeholder' => 'Buscar por nombre, apellidos, horario...',
    ])

    <div class="ip-card">
        <div class="ip-card-header">
            <span class="ip-table-summary">Mostrando {{ $alumnos->currentPage() }} de {{ $alumnos->lastPage() }}</span>
            <h5 class="ip-card-title">Listado de documentación</h5>
        </div>

        <div class="table-responsive">
            <table class="table ip-table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Alumno</th>
                        <th>Grado</th>
                        @foreach ($tipos as $tipo => $etiqueta)
                            <th class="text-center" title="{{ $etiqueta }}">{{ $shortLabels[$tipo] }}</th>
                        @endforeach
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($alumnos as $alumno)
                        <tr>
                            <td>{{ $alumno->id }}</td>
                            <td class="fw-semibold">{{ $alumno->nombre_completo }}</td>
                            <td>{{ $alumno->gradoEscolar->nombre ?? '—' }}</td>
                            @foreach ($tipos as $tipo => $etiqueta)
                                <td class="text-center">
                                    @if ($alumno->documentos->contains('tipo', $tipo))
                                        <span class="badge ip-badge-active" title="{{ $etiqueta }}">OK</span>
                                    @else
                                        <span class="badge text-bg-danger" title="{{ $etiqueta }}">Falta</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-end">
                                <a href="{{ route('documentacion.show', $alumno) }}" class="btn ip-btn-outline btn-sm">
                                    <i class="bi bi-folder2-open me-1"></i>Documentos
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 3 + count($tipos) + 1 }}" class="text-center ip-muted py-4">
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