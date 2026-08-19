<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->string('mes', 7)->index();
            $table->date('fecha')->nullable();
            $table->decimal('entrada_8am', 10, 2)->nullable();
            $table->decimal('pronto_pago', 10, 2)->nullable();
            $table->decimal('pago_normal', 10, 2)->nullable();
            $table->decimal('talleres', 10, 2)->nullable();
            $table->foreignId('forma_pago_id')->nullable()->constrained('formas_pago')->nullOnDelete();
            $table->timestamps();

            $table->unique(['alumno_id', 'mes']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
