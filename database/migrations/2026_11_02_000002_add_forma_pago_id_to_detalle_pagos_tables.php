<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos_talleres', function (Blueprint $table) {
            $table->foreignId('forma_pago_id')
                ->nullable()
                ->after('monto')
                ->constrained('formas_pago')
                ->nullOnDelete();
        });

        Schema::table('pagos_cursos', function (Blueprint $table) {
            $table->foreignId('forma_pago_id')
                ->nullable()
                ->after('monto')
                ->constrained('formas_pago')
                ->nullOnDelete();
        });

        Schema::table('pagos_lunch', function (Blueprint $table) {
            $table->foreignId('forma_pago_id')
                ->nullable()
                ->after('monto')
                ->constrained('formas_pago')
                ->nullOnDelete();
        });

        Schema::table('pagos_8am', function (Blueprint $table) {
            $table->foreignId('forma_pago_id')
                ->nullable()
                ->after('monto')
                ->constrained('formas_pago')
                ->nullOnDelete();
        });

        Schema::table('pagos_horarios_extendidos', function (Blueprint $table) {
            $table->foreignId('forma_pago_id')
                ->nullable()
                ->after('monto')
                ->constrained('formas_pago')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pagos_horarios_extendidos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('forma_pago_id');
        });

        Schema::table('pagos_8am', function (Blueprint $table) {
            $table->dropConstrainedForeignId('forma_pago_id');
        });

        Schema::table('pagos_lunch', function (Blueprint $table) {
            $table->dropConstrainedForeignId('forma_pago_id');
        });

        Schema::table('pagos_cursos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('forma_pago_id');
        });

        Schema::table('pagos_talleres', function (Blueprint $table) {
            $table->dropConstrainedForeignId('forma_pago_id');
        });
    }
};
