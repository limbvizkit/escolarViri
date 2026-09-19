<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmpleadoExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private Builder $query) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            '#', 'Nombre completo', 'Puesto', 'Sucursal', 'Horario', 'Fecha de nacimiento', 'Teléfonos', 'CURP', 'Estatus',
        ];
    }

    public function map($empleado): array
    {
        $telefonos = collect([
            $empleado->telefono_personal,
            $empleado->numeros_emergencia,
        ])->filter()->implode(' / ');

        return [
            $empleado->id,
            $empleado->nombre_completo,
            $empleado->puesto ?? '',
            $empleado->sucursal->nombre ?? '',
            $empleado->horario ?? '',
            $empleado->fecha_nacimiento?->format('d/m/Y') ?? '',
            $telefonos,
            $empleado->curp ?? '',
            $empleado->estatus_es_activo ? 'Activo' : 'Inactivo',
        ];
    }
}
