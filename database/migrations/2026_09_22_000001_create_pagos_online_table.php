<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_online', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portal_user_id')->constrained('portal_users')->cascadeOnDelete();
            $table->string('concepto', 255);
            $table->decimal('monto', 10, 2);
            $table->string('moneda', 3)->default('MXN');
            $table->string('order_id', 64)->unique();
            $table->string('openpay_charge_id', 64)->nullable()->unique();
            $table->string('estatus', 20)->default('pending');
            $table->string('authorization', 64)->nullable();
            $table->string('card_brand', 32)->nullable();
            $table->string('card_last4', 4)->nullable();
            $table->string('error_code', 32)->nullable();
            $table->string('error_category', 32)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['portal_user_id', 'estatus']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_online');
    }
};
