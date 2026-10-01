<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de pagos en línea</title>
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
        <h1>Listado de pagos en línea</h1>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Usuario</th>
                <th>Concepto</th>
                <th class="num">Monto</th>
                <th>Order ID</th>
                <th>OpenPay ID</th>
                <th>Autorización</th>
                <th>Tarjeta</th>
                <th>Estatus</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($payments as $payment)
                <tr>
                    <td>{{ $payment->id }}</td>
                    <td>
                        {{ $payment->portalUser->name ?? '—' }}
                        <br>
                        <span style="color: #6c757d; font-size: 9px;">{{ $payment->portalUser->email ?? '' }}</span>
                    </td>
                    <td>{{ $payment->concepto }}</td>
                    <td class="num">${{ number_format((float) $payment->monto, 2) }} {{ $payment->moneda }}</td>
                    <td>{{ $payment->order_id }}</td>
                    <td>{{ $payment->openpay_charge_id ?? '—' }}</td>
                    <td>{{ $payment->authorization ?? '—' }}</td>
                    <td>
                        @if ($payment->card_brand || $payment->card_last4)
                            {{ $payment->card_brand ? ucfirst($payment->card_brand) : '' }}{{ $payment->card_last4 ? ' terminada en '.$payment->card_last4 : '' }}
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $payment->statusLabel() }}</td>
                    <td>{{ $payment->created_at?->format('d/m/Y H:i') ?? '' }}</td>
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
