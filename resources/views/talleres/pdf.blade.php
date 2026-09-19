<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de inscripciones a talleres</title>
    <style>
        @page {
            margin: 18mm 10mm 18mm 10mm;
        }

        * {
            font-family: 'DejaVu Sans', sans-serif;
            box-sizing: border-box;
        }

        body {
            font-size: 10px;
            color: #1f2937;
        }

        .header {
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #0d6efd;
        }

        .header p {
            margin: 4px 0 0;
            color: #6c757d;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        table th {
            background-color: #0d6efd;
            color: #ffffff;
            text-align: left;
            padding: 6px;
            font-weight: 600;
        }

        table td {
            padding: 5px 6px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: top;
        }

        table tr:nth-child(even) td {
            background-color: #f4f6fb;
        }

        .num {
            text-align: right;
            white-space: nowrap;
        }

        .empty {
            text-align: center;
            color: #6c757d;
            padding: 30px 0;
            font-size: 12px;
        }

        .footer {
            position: fixed;
            bottom: -15mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #6c757d;
        }

        .footer .page::after {
            content: counter(page);
        }

        .footer .pages::after {
            content: counter(pages);
        }
    </style>
</head>
<body>
    <div class="footer">
        Página <span class="page"></span> de <span class="pages"></span>
    </div>

    <div class="header">
        <h1>Listado de inscripciones a talleres</h1>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Taller</th>
                <th>Alumno</th>
                <th>Grado Escolar</th>
                <th>Horario</th>
                <th class="num">Costo</th>
                <th class="num">Monto pagado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($inscripciones as $inscripcion)
                @php
                    $horario = substr($inscripcion->hora_inicio ?? '', 0, 5) . ' - ' . substr($inscripcion->hora_fin ?? '', 0, 5);
                @endphp
                <tr>
                    <td>{{ $inscripcion->taller->nombre ?? '—' }}</td>
                    <td>{{ $inscripcion->alumno->nombre_completo ?? '—' }}</td>
                    <td>{{ $inscripcion->alumno->gradoEscolar->nombre ?? '—' }}</td>
                    <td>{{ $horario }}</td>
                    <td class="num">${{ number_format((float) ($inscripcion->taller->costo ?? 0), 2) }}</td>
                    <td class="num">
                        {{ $inscripcion->monto_pagado !== null ? '$' . number_format((float) $inscripcion->monto_pagado, 2) : 'NA' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty">Sin registros</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
