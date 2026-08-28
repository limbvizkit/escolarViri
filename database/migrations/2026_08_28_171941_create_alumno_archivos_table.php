<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumno_archivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->string('archivo');
            $table->string('nombre_original')->nullable();
            $table->timestamps();
        });

        $this->backfillArchivosLegacy();
    }

    public function down(): void
    {
        Schema::dropIfExists('alumno_archivos');
    }

    private function backfillArchivosLegacy(): void
    {
        if (! Schema::hasColumn('alumnos', 'archivo')) {
            return;
        }

        $filas = DB::table('alumnos')
            ->whereNotNull('archivo')
            ->where('archivo', '!=', '')
            ->select(['id', 'archivo'])
            ->get();

        foreach ($filas as $fila) {
            DB::table('alumno_archivos')->insert([
                'alumno_id' => $fila->id,
                'archivo' => $fila->archivo,
                'nombre_original' => basename($fila->archivo),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
