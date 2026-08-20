<?php

use App\Models\Adeudo;
use App\Models\Alumno;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Adeudos iniciales por alumno.
     *
     * El alumno se resuelve por nombre y apellido paterno (no por ID, que
     * depende del entorno): [nombre, apellido_paterno, [[concepto, monto, anotaciones], ...]].
     *
     * @return array<int, array{0: string, 1: string, 2: array<int, array{0: string, 1: int, 2: string|null}>}>
     */
    private function data(): array
    {
        return [
            ['ZIAH FRASER', 'HUERTA', [
                ['Colegiatura de Enero 2026', 4763, null],
                ['Colegiatura de Febrero 2026', 4763, null],
                ['Colegiatura de Marzo 2026', 4763, null],
                ['Colegiatura de Abril 2026', 4763, null],
                ['Colegiatura de Mayo 2026', 4763, null],
                ['Colegiatura de Junio 2026', 4763, null],
                ['Colegiatura de Julio 2026', 4763, null],
                ['Reinscripcion ciclo 26-27', 7500, null],
                ['Talleres oct-feb', 5776, null],
            ]],
            ['BRUNO', 'PIANA', [
                ['Colegiatura de Julio 2026', 9982, null],
            ]],
            ['DIEGO', 'ESCOBAR', [
                ['Colegiatura de Junio 2026', 10981, null],
                ['Colegiatura de Julio 2026', 9982, null],
                ['Curso de verano', 2700, null],
            ]],
            ['CONSTANZA', 'CORDERO', [
                ['Colegiatura de Abril 2026', 4239, null],
                ['Colegiatura de Mayo 2026', 6126, null],
                ['Colegiatura de Junio 2026', 5569, null],
                ['Colegiatura de Julio 2026', 5063, null],
            ]],
            ['EMILIO ALEXANDER', 'WILSON', [
                ['Colegiatura de Julio 2026', 7425, null],
                ['Reinscripcion ciclo 26-27', 7500, null],
                ['Colegiatura de Agosto 2026', 5000, null],
                ['Cuota de materiales', 7650, null],
            ]],
            ['LEONARDO', 'MARTINEZ', [
                ['Colegiatura de Mayo 2026', 6739, null],
                ['Colegiatura de Junio 2026', 6058, null],
                ['Colegiatura de Julio 2026', 5569, null],
                ['Curso de verano', 50, '1 dia'],
            ]],
        ];
    }

    public function up(): void
    {
        foreach ($this->data() as [$nombre, $paterno, $adeudos]) {
            $alumno = Alumno::where('nombre', 'like', $nombre.'%')
                ->where('apellido_paterno', $paterno)
                ->first();

            if (! $alumno) {
                fwrite(STDERR, "Advertencia: alumno no encontrado, se omiten sus adeudos: {$nombre} {$paterno}\n");

                continue;
            }

            foreach ($adeudos as [$concepto, $monto, $anotaciones]) {
                Adeudo::firstOrCreate(
                    ['alumno_id' => $alumno->id, 'concepto' => $concepto],
                    [
                        'monto' => $monto,
                        'anotaciones' => $anotaciones,
                        'monto_pagado' => 0,
                        'estatus' => Adeudo::ESTATUS_PENDIENTE,
                    ],
                );
            }
        }
    }

    public function down(): void
    {
        foreach ($this->data() as [$nombre, $paterno, $adeudos]) {
            $alumno = Alumno::where('nombre', 'like', $nombre.'%')
                ->where('apellido_paterno', $paterno)
                ->first();

            if (! $alumno) {
                continue;
            }

            foreach ($adeudos as [$concepto, $monto, $anotaciones]) {
                Adeudo::where('alumno_id', $alumno->id)
                    ->where('concepto', $concepto)
                    ->where('monto', $monto)
                    ->where('anotaciones', $anotaciones)
                    ->delete();
            }
        }
    }
};
