<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AdeudoExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private Builder $query) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            '#', 'Alumno', 'Concepto', 'Monto', 'Abonado', 'Pendiente', 'Estatus',
        ];
    }

    public function map($adeudo): array
    {
        return [
            $adeudo->id,
            $adeudo->alumno->nombre_completo ?? '',
            $adeudo->concepto,
            (float) $adeudo->monto,
            (float) $adeudo->monto_pagado,
            max(0.0, (float) $adeudo->monto - (float) $adeudo->monto_pagado),
            ucfirst($adeudo->estatus),
        ];
    }
}
