<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de pagos</title>
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
        <h1>Listado de pagos</h1>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Alumno</th>
                <th>Grado Escolar</th>
                <th>Mes</th>
                <th>Fecha</th>
                <th class="num">Entrada 8AM</th>
                <th class="num">Pronto pago</th>
                <th class="num">Pago normal</th>
                <th class="num">Talleres</th>
                <th>Forma de pago</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pagos as $pago)
                <tr>
                    <td>{{ $pago->id }}</td>
                    <td>{{ $pago->alumno->nombre_completo }}</td>
                    <td>{{ $pago->alumno->gradoEscolar->nombre ?? '—' }}</td>
                    <td>{{ App\Models\Pago::mesLabel($pago->mes) }}</td>
                    <td>{{ $pago->fecha?->format('d/m/Y') ?? '—' }}</td>
                    <td class="num">{{ $pago->entrada_8am !== null ? '$' . number_format((float) $pago->entrada_8am, 2) : '—' }}</td>
                    <td class="num">{{ $pago->pronto_pago !== null ? '$' . number_format((float) $pago->pronto_pago, 2) : '—' }}</td>
                    <td class="num">{{ $pago->pago_normal !== null ? '$' . number_format((float) $pago->pago_normal, 2) : '—' }}</td>
                    <td class="num">{{ $pago->talleres !== null ? '$' . number_format((float) $pago->talleres, 2) : '—' }}</td>
                    <td>{{ $pago->formaPago->nombre ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="empty">Sin registros</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>