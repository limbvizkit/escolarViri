<?php

namespace App\Exports;

use App\Models\Pago;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PagoExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private Builder $query) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            '#', 'Alumno', 'Grado Escolar', 'Mes', 'Fecha', 'Entrada 8AM',
            'Pronto pago', 'Pago normal', 'Talleres', 'Lunch', 'Cursos', 'Fotos',
            'Horario extendido', 'Inscripción', 'Re/Inscripción', 'Materiales', 'NatGeo', 'Entrevista',
            'Forma de pago',
        ];
    }

    public function map($pago): array
    {
        return [
            $pago->id,
            $pago->alumno->nombre_completo,
            $pago->alumno->gradoEscolar->nombre ?? '',
            Pago::mesLabel($pago->mes),
            $pago->fecha?->format('d/m/Y') ?? '',
            $pago->entrada_8am !== null ? (float) $pago->entrada_8am : '',
            $pago->pronto_pago !== null ? (float) $pago->pronto_pago : '',
            $pago->pago_normal !== null ? (float) $pago->pago_normal : '',
            $pago->talleres !== null ? (float) $pago->talleres : '',
            $pago->lunch !== null ? (float) $pago->lunch : '',
            $pago->cursos !== null ? (float) $pago->cursos : '',
            $pago->fotos !== null ? (float) $pago->fotos : '',
            $pago->horario_extendido !== null ? (float) $pago->horario_extendido : '',
            $pago->inscripcion !== null ? (float) $pago->inscripcion : '',
            $pago->reinscripcion !== null ? (float) $pago->reinscripcion : '',
            $pago->materiales !== null ? (float) $pago->materiales : '',
            $pago->natgeo !== null ? (float) $pago->natgeo : '',
            $pago->entrevista !== null ? (float) $pago->entrevista : '',
            $pago->formaPago->nombre ?? '',
        ];
    }
}
