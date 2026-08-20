<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->string('tipo');
            $table->string('archivo');
            $table->timestamps();

            $table->unique(['alumno_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
