<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nivel_id')->constrained('niveles')->onDelete('cascade');
            $table->string('nombre');
            $table->string('apellido_paterno');
            $table->string('apellido_materno')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('horario')->nullable();
            $table->decimal('inscripcion', 10, 2)->nullable();
            $table->decimal('reinscripcion', 10, 2)->nullable();
            $table->decimal('entrevista_inicial', 10, 2)->nullable();
            $table->decimal('nat_geo', 10, 2)->nullable();
            $table->decimal('cuota_materiales', 10, 2)->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->decimal('cuota_mensual', 10, 2)->nullable();
            $table->boolean('estatus')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};
