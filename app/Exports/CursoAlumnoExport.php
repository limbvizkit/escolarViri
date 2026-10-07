<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CursoAlumnoExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private Builder $query) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Curso', 'Alumno', 'Grado Escolar', 'Fecha inicio', 'Fecha fin', 'Horario', 'Costo',
        ];
    }

    public function map($inscripcion): array
    {
        $horario = substr((string) $inscripcion->curso?->hora_inicio, 0, 5).' - '.substr((string) $inscripcion->curso?->hora_fin, 0, 5);

        return [
            $inscripcion->curso->nombre ?? '',
            $inscripcion->alumno->nombre_completo ?? '',
            $inscripcion->alumno->gradoEscolar->nombre ?? '',
            $inscripcion->curso?->fecha_inicio?->format('d/m/Y') ?? '',
            $inscripcion->curso?->fecha_fin?->format('d/m/Y') ?? '',
            $horario,
            (float) ($inscripcion->curso->costo ?? 0),
        ];
    }
}
