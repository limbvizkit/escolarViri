<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class AdeudoExport implements FromQuery, ShouldAutoSize, WithCustomStartCell, WithEvents, WithHeadings, WithMapping
{
    private const SCHOOL_NAME = 'Learning Play House';

    private const LOGO_PATH = 'img/logo.jpg';

    public function __construct(private Builder $query) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function startCell(): string
    {
        return 'A3';
    }

    public function headings(): array
    {
        return [
            '#', 'Alumno', 'Concepto', 'Mes', 'Año', 'Monto', 'Abonado', 'Pendiente', 'Estatus',
        ];
    }

    public function map($adeudo): array
    {
        return [
            $adeudo->id,
            $adeudo->alumno->nombre_completo ?? '',
            $adeudo->concepto,
            $adeudo->mes ? $adeudo->mes_nombre : '',
            $adeudo->anio ?? '',
            (float) $adeudo->monto,
            (float) $adeudo->monto_pagado,
            max(0.0, (float) $adeudo->monto - (float) $adeudo->monto_pagado),
            ucfirst($adeudo->estatus),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->setCellValue('C1', self::SCHOOL_NAME);
                $sheet->mergeCells('C1:F1');
                $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('C1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getRowDimension(1)->setRowHeight(50);

                $logo = public_path(self::LOGO_PATH);

                if (! is_file($logo)) {
                    return;
                }

                $drawing = new Drawing;
                $drawing->setPath($logo);
                $drawing->setHeight(44);
                $drawing->setCoordinates('A1');
                $drawing->setOffsetX(6);
                $drawing->setOffsetY(3);
                $drawing->setWorksheet($sheet);
            },
        ];
    }
}
