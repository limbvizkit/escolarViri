<?php

namespace Database\Seeders;

use App\Models\GradoEscolar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GradoEscolarSeeder extends Seeder
{
    public function run(): void
    {
        $gradosEscolares = [
            'ESTIMULACIÓN TEMPRANA',
            'PRE-MATERNAL',
            'MATERNAL',
            'KINDER 1',
            'KINDER 2-A',
            'KINDER 2-B',
            'KINDER 3',
            'PRE-FIRST',
        ];

        foreach ($gradosEscolares as $nombre) {
            GradoEscolar::updateOrCreate(
                ['slug' => Str::slug($nombre)],
                ['nombre' => $nombre],
            );
        }
    }
}
