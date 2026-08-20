<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de alumnos</title>
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

        .center {
            text-align: center;
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
        <h1>Listado de alumnos</h1>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre completo</th>
                <th>Grado Escolar</th>
                <th>Sucursal</th>
                <th>Fecha nacimiento</th>
                <th>Horario</th>
                <th class="num">Inscripción</th>
                <th class="num">Entrevista</th>
                <th class="num">Nat Geo</th>
                <th class="num">Cuota materiales</th>
                <th class="num">Cuota mensual</th>
                <th class="center">Estatus</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($alumnos as $alumno)
                <tr>
                    <td>{{ $alumno->id }}</td>
                    <td>{{ $alumno->nombre_completo }}</td>
                    <td>{{ $alumno->gradoEscolar->nombre ?? '—' }}</td>
                    <td>{{ $alumno->sucursal->nombre ?? '—' }}</td>
                    <td>{{ $alumno->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $alumno->horario ?? '—' }}</td>
                    <td class="num">{{ $alumno->inscripcion ? '$' . number_format((float) $alumno->inscripcion, 2) : 'NA' }}</td>
                    <td class="num">{{ $alumno->entrevista_inicial ? '$' . number_format((float) $alumno->entrevista_inicial, 2) : 'NA' }}</td>
                    <td class="num">{{ $alumno->nat_geo ? '$' . number_format((float) $alumno->nat_geo, 2) : 'NA' }}</td>
                    <td class="num">{{ $alumno->cuota_materiales ? '$' . number_format((float) $alumno->cuota_materiales, 2) : 'NA' }}</td>
                    <td class="num">{{ $alumno->cuota_mensual ? '$' . number_format((float) $alumno->cuota_mensual, 2) : 'NA' }}</td>
                    <td class="center">{{ $alumno->estatus_es_activo ? 'Activo' : 'Inactivo' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="empty">Sin registros</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>