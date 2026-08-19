<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\FormaPago;
use App\Models\Pago;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PagoSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('pagos.csv');

        if (! File::exists($path)) {
            $this->command?->error('No se encontró el archivo pagos.csv');

            return;
        }

        $alumnos = Alumno::orderBy('id')->get();
        $formas = FormaPago::all(['id', 'nombre']);

        $handle = fopen($path, 'r');
        $bloquesMeses = [];
        $idxAlumno = 0;
        $importados = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $row = array_map(fn ($v) => trim($v ?? ''), $row);

            if (count(array_filter($row)) === 0) {
                continue;
            }

            $primeraCol = $row[0] ?? '';

            if (in_array(strtolower($primeraCol), ['#', 'id'], true)) {
                continue;
            }

            if (is_numeric($primeraCol)) {
                $alumno = $alumnos[$idxAlumno] ?? null;
                $idxAlumno++;

                if ($alumno === null || trim($row[1] ?? '') === '') {
                    continue;
                }

                foreach ($bloquesMeses as $k => $mes) {
                    if ($mes === '') {
                        continue;
                    }

                    $base = 11 + 6 * $k;
                    $fecha = $this->parseFecha($row[$base] ?? '');
                    $entrada = $this->parseMonto($row[$base + 1] ?? '');
                    $pronto = $this->parseMonto($row[$base + 2] ?? '');
                    $normal = $this->parseMonto($row[$base + 3] ?? '');
                    $formaPago = $this->matchFormaPago($row[$base + 4] ?? '', $formas);
                    $talleres = $this->parseMonto($row[$base + 5] ?? '');

                    if ($fecha === null
                        && $entrada === null
                        && $pronto === null
                        && $normal === null
                        && $talleres === null
                        && $formaPago === null) {
                        continue;
                    }

                    Pago::updateOrCreate(
                        ['alumno_id' => $alumno->id, 'mes' => $this->mesANio($mes)],
                        [
                            'fecha' => $fecha,
                            'entrada_8am' => $entrada,
                            'pronto_pago' => $pronto,
                            'pago_normal' => $normal,
                            'forma_pago_id' => $formaPago,
                            'talleres' => $talleres,
                        ],
                    );

                    $importados++;
                }

                continue;
            }

            // Línea de grado escolar: primera columna con texto y base (cols 1..10) vacía.
            // También define los bloques de meses visibles en esa sección.
            $base = array_slice($row, 1, 10);

            if (count(array_filter($base)) === 0) {
                $bloquesMeses = [];

                for ($k = 0; ; $k++) {
                    $val = $row[11 + 6 * $k] ?? '';

                    if ($val === '' || ! preg_match('/^[a-z]{3}-\d{2,4}$/i', $val)) {
                        break;
                    }

                    $bloquesMeses[] = $val;
                }
            }
        }

        fclose($handle);

        $this->command?->info("Pagos importados: {$importados}");
    }

    private function mesANio(string $mes): string
    {
        $meses = [
            'ene' => '01', 'feb' => '02', 'mar' => '03', 'abr' => '04', 'may' => '05', 'jun' => '06',
            'jul' => '07', 'ago' => '08', 'sep' => '09', 'oct' => '10', 'nov' => '11', 'dic' => '12',
        ];

        if (preg_match('/^([a-z]{3})-(\d{2,4})$/i', $mes, $m)) {
            $num = $meses[strtolower($m[1])] ?? null;
            $anio = (int) $m[2];

            if ($anio < 100) {
                $anio += 2000;
            }

            if ($num !== null) {
                return sprintf('%04d-%s', $anio, $num);
            }
        }

        return $mes;
    }

    private function parseFecha(string $valor): ?string
    {
        $valor = trim($valor);

        if ($valor === '' || strtoupper($valor) === 'NA') {
            return null;
        }

        if (! preg_match('#^(\d{1,2})[/-](\d{1,2})[/-](\d{4})$#', $valor, $m)) {
            return null;
        }

        $dia = (int) $m[1];
        $mes = (int) $m[2];
        $anio = (int) $m[3];

        if (! checkdate($mes, $dia, $anio)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
    }

    private function parseMonto(string $valor): ?float
    {
        $limpio = preg_replace('/[^0-9.]/', '', trim($valor));

        if ($limpio === '' || $limpio === null) {
            return null;
        }

        return round((float) $limpio, 2);
    }

    private function matchFormaPago(string $valor, $formas): ?int
    {
        $valor = trim($valor);

        if ($valor === '' || strtoupper($valor) === 'NA') {
            return null;
        }

        foreach ($formas as $forma) {
            if (strcasecmp($forma->nombre, $valor) === 0) {
                return $forma->id;
            }
        }

        return null;
    }
}
