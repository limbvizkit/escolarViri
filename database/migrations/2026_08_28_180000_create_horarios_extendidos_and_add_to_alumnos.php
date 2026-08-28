<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios_extendidos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('estatus_id')->nullable()->default(1)->constrained('estatus')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('alumnos', function (Blueprint $table) {
            $table->foreignId('horario_extendido_id')
                ->nullable()
                ->after('horario')
                ->constrained('horarios_extendidos')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('horario_extendido_id');
        });

        Schema::dropIfExists('horarios_extendidos');
    }
};
