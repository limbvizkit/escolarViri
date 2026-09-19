<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TallerAlumnoExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private Builder $query) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Taller', 'Alumno', 'Grado Escolar', 'Horario', 'Costo', 'Monto pagado',
        ];
    }

    public function map($inscripcion): array
    {
        $horario = substr($inscripcion->hora_inicio ?? '', 0, 5).' - '.substr($inscripcion->hora_fin ?? '', 0, 5);

        return [
            $inscripcion->taller->nombre ?? '',
            $inscripcion->alumno->nombre_completo ?? '',
            $inscripcion->alumno->gradoEscolar->nombre ?? '',
            $horario,
            (float) ($inscripcion->taller->costo ?? 0),
            $inscripcion->monto_pagado !== null ? (float) $inscripcion->monto_pagado : 'NA',
        ];
    }
}
