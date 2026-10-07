<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AlumnoExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private Builder $query) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            '#', 'Nombre', 'Apellido paterno', 'Apellido materno', 'Sexo', 'Grado Escolar', 'Sucursal', 'Fecha nacimiento',
            'Horario', 'Inscripción', 'Re/Inscripción', 'Entrevista', 'Nat Geo', 'Cuota materiales',
            'Fecha ingreso', 'Cuota mensual', 'Estatus',
        ];
    }

    public function map($alumno): array
    {
        return [
            $alumno->id,
            $alumno->nombre,
            $alumno->apellido_paterno,
            $alumno->apellido_materno ?? '',
            $alumno->sexo_label ?? '',
            $alumno->gradoEscolar->nombre ?? '',
            $alumno->sucursal->nombre ?? '',
            $alumno->fecha_nacimiento?->format('d/m/Y') ?? '',
            $alumno->horario ?? '',
            $alumno->inscripcion ?: '',
            $alumno->reinscripcion ?: '',
            $alumno->entrevista_inicial ?: '',
            $alumno->nat_geo ?: '',
            $alumno->cuota_materiales ?: '',
            $alumno->fecha_ingreso?->format('d/m/Y') ?? '',
            $alumno->cuota_mensual !== null ? (float) $alumno->cuota_mensual : 'NA',
            $alumno->estatus_es_activo ? 'Activo' : 'Inactivo',
        ];
    }
}
