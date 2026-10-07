<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columnas del alumno que pasan de monto (decimal) a estado (texto).
     *
     * @var array<int, string>
     */
    private array $columnas = [
        'inscripcion',
        'reinscripcion',
        'entrevista_inicial',
        'nat_geo',
        'cuota_materiales',
    ];

    /**
     * Mapeo columna del alumno => concepto equivalente en pagos.
     *
     * @var array<string, string>
     */
    private array $mapaPago = [
        'inscripcion' => 'inscripcion',
        'reinscripcion' => 'reinscripcion',
        'entrevista_inicial' => 'entrevista',
        'nat_geo' => 'natgeo',
        'cuota_materiales' => 'materiales',
    ];

    public function up(): void
    {
        // 1. Recordar qué alumnos tenían un monto por columna antes de
        //    reemplazar los valores por estados. Se considera "con valor"
        //    cualquier número almacenado, incluido el 0 (un 0 es un monto).
        $conValor = array_fill_keys($this->columnas, []);

        foreach (DB::table('alumnos')->select(array_merge(['id'], $this->columnas))->orderBy('id')->get() as $alumno) {
            foreach ($this->columnas as $columna) {
                $valor = $alumno->{$columna};

                if ($valor !== null && $valor !== '') {
                    $conValor[$columna][] = $alumno->id;
                }
            }
        }

        $alumnosConValor = array_values(array_unique(array_merge(...array_values($conValor))));

        // 2. Garantizar que cada alumno con algún monto tenga un pago donde
        //    recibirlo. Si no tiene ninguno, se crea uno en el mes actual.
        $mesActual = now()->format('Y-m');
        foreach ($alumnosConValor as $alumnoId) {
            $tienePago = DB::table('pagos')->where('alumno_id', $alumnoId)->exists();

            if (! $tienePago) {
                DB::table('pagos')->insert([
                    'alumno_id' => $alumnoId,
                    'mes' => $mesActual,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. Mover cada monto al primer pago del alumno (el de menor id).
        //    Solo se rellena si el concepto destino sigue vacío.
        $primerosPagos = DB::table('pagos')
            ->select('alumno_id', DB::raw('MIN(id) as pago_id'))
            ->groupBy('alumno_id')
            ->pluck('pago_id', 'alumno_id');

        foreach ($primerosPagos as $alumnoId => $pagoId) {
            $alumno = DB::table('alumnos')->where('id', $alumnoId)->first();
            $pago = DB::table('pagos')->where('id', $pagoId)->first();

            if ($alumno === null || $pago === null) {
                continue;
            }

            $actualizar = [];
            foreach ($this->mapaPago as $columnaAlumno => $columnaPago) {
                $monto = $alumno->{$columnaAlumno};

                if ($monto !== null && $monto !== '' && $pago->{$columnaPago} === null) {
                    $actualizar[$columnaPago] = $monto;
                }
            }

            if ($actualizar !== []) {
                DB::table('pagos')->where('id', $pagoId)->update($actualizar);
            }
        }

        // 4. Cambiar el tipo de columna de decimal a texto (estado).
        Schema::table('alumnos', function (Blueprint $table) {
            foreach ($this->columnas as $columna) {
                $table->string($columna, 20)->nullable()->change();
            }
        });

        // 5. Asignar estados: conserva la semántica previa (monto => aplica,
        //    nulo => no aplica).
        foreach ($this->columnas as $columna) {
            DB::table('alumnos')->update([$columna => 'NO APLICA']);

            if ($conValor[$columna] !== []) {
                DB::table('alumnos')->whereIn('id', $conValor[$columna])->update([$columna => 'SI']);
            }
        }
    }

    public function down(): void
    {
        // Se limpian los estados antes de volver a tipo decimal.
        foreach ($this->columnas as $columna) {
            DB::table('alumnos')->update([$columna => null]);
        }

        Schema::table('alumnos', function (Blueprint $table) {
            foreach ($this->columnas as $columna) {
                $table->decimal($columna, 10, 2)->nullable()->change();
            }
        });
    }
};
