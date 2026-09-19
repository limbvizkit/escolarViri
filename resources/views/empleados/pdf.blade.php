<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de empleados</title>
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
        <h1>Listado de empleados</h1>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre completo</th>
                <th>Puesto</th>
                <th>Sucursal</th>
                <th>Horario</th>
                <th>Fecha de nacimiento</th>
                <th>Teléfonos</th>
                <th>CURP</th>
                <th>Estatus</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($empleados as $empleado)
                @php
                    $telefonos = collect([
                        $empleado->telefono_personal,
                        $empleado->numeros_emergencia,
                    ])->filter()->implode(' / ');
                @endphp
                <tr>
                    <td>{{ $empleado->id }}</td>
                    <td>{{ $empleado->nombre_completo }}</td>
                    <td>{{ $empleado->puesto ?? '—' }}</td>
                    <td>{{ $empleado->sucursal->nombre ?? '—' }}</td>
                    <td>{{ $empleado->horario ?? '—' }}</td>
                    <td>{{ $empleado->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $telefonos ?: '—' }}</td>
                    <td>{{ $empleado->curp ?? '—' }}</td>
                    <td>{{ $empleado->estatus_es_activo ? 'Activo' : 'Inactivo' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="empty">Sin registros</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
