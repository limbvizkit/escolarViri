<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curso_alumno', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['curso_id', 'alumno_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curso_alumno');
    }
};
