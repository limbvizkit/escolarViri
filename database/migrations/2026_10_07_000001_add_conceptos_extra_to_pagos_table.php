<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->decimal('cursos', 10, 2)->nullable()->after('lunch');
            $table->decimal('fotos', 10, 2)->nullable()->after('cursos');
            $table->decimal('horario_extendido', 10, 2)->nullable()->after('fotos');
            $table->decimal('inscripcion', 10, 2)->nullable()->after('horario_extendido');
            $table->decimal('materiales', 10, 2)->nullable()->after('inscripcion');
            $table->decimal('natgeo', 10, 2)->nullable()->after('materiales');
            $table->decimal('entrevista', 10, 2)->nullable()->after('natgeo');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn([
                'cursos',
                'fotos',
                'horario_extendido',
                'inscripcion',
                'materiales',
                'natgeo',
                'entrevista',
            ]);
        });
    }
};
