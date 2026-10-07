<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PagoCursoExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private Builder $query) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return ['#', 'Alumno', 'Grado', 'Curso', 'Mes', 'Monto', 'Observaciones', 'Registrado'];
    }

    public function map($pago): array
    {
        return [
            $pago->id,
            $pago->alumno->nombre_completo ?? '',
            $pago->alumno->gradoEscolar->nombre ?? '',
            $pago->curso->nombre ?? '',
            $pago->mes_label,
            (float) $pago->monto,
            $pago->observaciones ?? '',
            $pago->created_at?->format('d/m/Y H:i') ?? '',
        ];
    }
}
