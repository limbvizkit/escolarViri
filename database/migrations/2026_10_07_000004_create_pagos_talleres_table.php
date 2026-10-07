<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_talleres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->foreignId('taller_id')->constrained('talleres')->cascadeOnDelete();
            $table->string('mes', 7)->index();
            $table->decimal('monto', 10, 2);
            $table->string('observaciones', 1000)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_talleres');
    }
};
