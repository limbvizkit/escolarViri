<?php

namespace Database\Seeders;

use App\Models\FormaPago;
use Illuminate\Database\Seeder;

class FormaPagoSeeder extends Seeder
{
    public function run(): void
    {
        $formas = ['Transferencia', 'Terminal', 'Open pay', 'Efectivo'];

        foreach ($formas as $forma) {
            FormaPago::firstOrCreate(['nombre' => $forma]);
        }
    }
}
