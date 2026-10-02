<?php

namespace Database\Factories;

use App\Models\PagoOnline;
use App\Models\PortalUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PagoOnline>
 */
class PagoOnlineFactory extends Factory
{
    protected $model = PagoOnline::class;

    public function definition(): array
    {
        return [
            'portal_user_id' => PortalUser::factory(),
            'concepto' => fake()->sentence(3),
            'monto' => fake()->randomFloat(2, 100, 5000),
            'moneda' => 'MXN',
            'order_id' => 'PO-'.date('Ymd-His').'-'.strtoupper(fake()->bothify('??????')),
            'openpay_charge_id' => null,
            'estatus' => PagoOnline::STATUS_COMPLETED,
            'authorization' => null,
            'card_brand' => null,
            'card_last4' => null,
            'error_code' => null,
            'error_category' => null,
            'error_message' => null,
        ];
    }
}
