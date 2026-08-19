<?php

namespace Database\Seeders;

use App\Models\Alumno;
use App\Models\GradoEscolar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AlumnoSeeder extends Seeder
{
    public function run(): void
    {
        $path = base_path('alumnosViri.csv');

        if (! File::exists($path)) {
            $this->command?->error('No se encontró el archivo alumnosViri.csv');

            return;
        }

        $gradosEscolares = GradoEscolar::pluck('id', 'slug');

        $handle = fopen($path, 'r');

        $gradoEscolarActual = null;
        $headers = null;
        $importados = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $row = array_map(fn ($v) => trim($v ?? ''), $row);

            if (count(array_filter($row)) === 0) {
                continue;
            }

            $primeraCol = $row[0] ?? '';
            $resto = array_slice($row, 1);
            $restoVacio = count(array_filter($resto)) === 0;

            // Línea de grado escolar: primera columna con texto y el resto vacío
            if ($primeraCol !== '' && $restoVacio && ! is_numeric($primeraCol)) {
                $gradoEscolarActual = $gradosEscolares[Str::slug($primeraCol)] ?? null;

                continue;
            }

            // Línea de encabezados de columnas
            if (strtolower($primeraCol) === 'id' && str_contains(strtoupper($row[1] ?? ''), 'ALUMNO')) {
                continue;
            }

            if ($gradoEscolarActual === null) {
                continue;
            }

            $nombreCompleto = strtoupper($row[1] ?? '');

            if ($nombreCompleto === '') {
                continue;
            }

            [$nombre, $apellidoPaterno, $apellidoMaterno] = $this->separarNombre($nombreCompleto);

            Alumno::updateOrCreate(
                [
                    'grado_escolar_id' => $gradoEscolarActual,
                    'nombre' => $nombre,
                    'apellido_paterno' => $apellidoPaterno,
                    'apellido_materno' => $apellidoMaterno,
                ],
                [
                    'fecha_nacimiento' => $this->parseFecha($row[2] ?? ''),
                    'horario' => $this->parseHorario($row[3] ?? ''),
                    'inscripcion' => $this->parseMonto($row[4] ?? ''),
                    'reinscripcion' => $this->parseMonto($row[5] ?? ''),
                    'entrevista_inicial' => $this->parseMonto($row[6] ?? ''),
                    'nat_geo' => $this->parseMonto($row[7] ?? ''),
                    'cuota_materiales' => $this->parseMonto($row[8] ?? ''),
                    'fecha_ingreso' => $this->parseFecha($row[9] ?? ''),
                    'cuota_mensual' => $this->parseMonto($row[10] ?? ''),
                    'estatus' => true,
                ],
            );

            $importados++;
        }

        fclose($handle);

        $this->command?->info("Alumnos importados: {$importados}");
    }

    private function separarNombre(string $nombreCompleto): array
    {
        $partes = preg_split('/\s+/', $nombreCompleto);
        $total = count($partes);

        if ($total >= 3) {
            $nombre = implode(' ', array_slice($partes, 0, $total - 2));

            return [$nombre, $partes[$total - 2], $partes[$total - 1]];
        }

        if ($total === 2) {
            return [$partes[0], $partes[1], ''];
        }

        return [$partes[0] ?? '', '', ''];
    }

    private function parseFecha(string $valor): ?string
    {
        $valor = trim($valor);

        if ($valor === '') {
            return null;
        }

        $meses = [
            'ene' => '01', 'feb' => '02', 'mar' => '03', 'abr' => '04', 'may' => '05', 'jun' => '06',
            'jul' => '07', 'ago' => '08', 'sep' => '09', 'oct' => '10', 'nov' => '11', 'dic' => '12',
        ];

        if (preg_match('/^(\d{1,2})-([a-z]{3})-(\d{2})$/i', $valor, $m)) {
            $dia = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $mes = $meses[strtolower($m[2])] ?? null;
            $anio = 2000 + (int) $m[3];

            if ($mes !== null && checkdate((int) $mes, (int) $m[1], $anio)) {
                return "{$anio}-{$mes}-{$dia}";
            }
        }

        return null;
    }

    private function parseHorario(string $valor): ?string
    {
        $valor = trim($valor);

        if ($valor === '') {
            return null;
        }

        // Normaliza "8:15:1:45" (error de tipeo) a "8:15-1:45"
        if (preg_match('/^(\d{1,2}:\d{2}):(\d{1,2}:\d{2})$/', $valor, $m)) {
            $valor = $m[1].'-'.$m[2];
        }

        return $valor;
    }

    private function parseMonto(string $valor): ?float
    {
        $limpio = preg_replace('/[^0-9.]/', '', trim($valor));

        if ($limpio === '' || $limpio === null) {
            return null;
        }

        return round((float) $limpio, 2);
    }
}
