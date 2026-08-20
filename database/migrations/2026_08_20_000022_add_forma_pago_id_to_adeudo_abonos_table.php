<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adeudo_abonos', function (Blueprint $table) {
            $table->foreignId('forma_pago_id')
                ->nullable()
                ->after('adeudo_id')
                ->constrained('formas_pago')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('adeudo_abonos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('forma_pago_id');
        });
    }
};
