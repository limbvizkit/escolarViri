<?php

namespace App\Exports;

use App\Models\PagoOnline;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OnlinePaymentExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private Builder $query) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            '#', 'Usuario', 'Correo', 'Concepto', 'Monto', 'Moneda', 'Order ID', 'OpenPay ID', 'Autorización', 'Tarjeta', 'Estatus', 'Fecha',
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->id,
            $payment->portalUser->name ?? '',
            $payment->portalUser->email ?? '',
            $payment->concepto,
            (float) $payment->monto,
            $payment->moneda,
            $payment->order_id,
            $payment->openpay_charge_id ?? '',
            $payment->authorization ?? '',
            $this->cardLabel($payment),
            $payment->statusLabel(),
            $payment->created_at?->format('d/m/Y H:i') ?? '',
        ];
    }

    private function cardLabel(PagoOnline $payment): string
    {
        $parts = [];

        if ($payment->card_brand) {
            $parts[] = ucfirst($payment->card_brand);
        }

        if ($payment->card_last4) {
            $parts[] = 'terminada en '.$payment->card_last4;
        }

        return implode(' ', $parts);
    }
}
