<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adeudos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->string('concepto');
            $table->text('anotaciones')->nullable();
            $table->decimal('monto', 10, 2);
            $table->decimal('monto_pagado', 10, 2)->default(0);
            $table->string('estatus', 20)->default('pendiente');
            $table->timestamps();
        });

        Schema::create('adeudo_abonos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adeudo_id')->constrained('adeudos')->cascadeOnDelete();
            $table->decimal('monto', 10, 2);
            $table->date('fecha');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adeudo_abonos');
        Schema::dropIfExists('adeudos');
    }
};
