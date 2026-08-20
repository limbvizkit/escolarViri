<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLAS = [
        'escuelas',
        'sucursales',
        'empleados',
        'grados_escolares',
        'alumnos',
        'formas_pago',
    ];

    public function up(): void
    {
        foreach (self::TABLAS as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->foreignId('estatus_id')->nullable()->default(1)
                    ->after('estatus')
                    ->constrained('estatus')
                    ->nullOnDelete();
            });

            DB::table($tabla)->where('estatus', true)->update(['estatus_id' => 1]);
            DB::table($tabla)->where('estatus', false)->update(['estatus_id' => 2]);

            Schema::table($tabla, function (Blueprint $table) {
                $table->dropColumn('estatus');
            });
        }
    }

    public function down(): void
    {
        foreach (self::TABLAS as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->boolean('estatus')->default(true)->after('estatus_id');
            });

            DB::table($tabla)->where('estatus_id', 1)->update(['estatus' => true]);
            DB::table($tabla)->where('estatus_id', '!=', 1)->update(['estatus' => false]);

            Schema::table($tabla, function (Blueprint $table) {
                $table->dropConstrainedForeignId('estatus_id');
            });
        }
    }
};
