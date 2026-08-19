<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropForeign(['nivel_id']);
        });

        Schema::rename('niveles', 'grados_escolares');

        Schema::table('alumnos', function (Blueprint $table) {
            $table->renameColumn('nivel_id', 'grado_escolar_id');
        });

        Schema::table('alumnos', function (Blueprint $table) {
            $table->foreign('grado_escolar_id')->references('id')->on('grados_escolares')->onDelete('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropForeign(['grado_escolar_id']);
        });

        Schema::table('alumnos', function (Blueprint $table) {
            $table->renameColumn('grado_escolar_id', 'nivel_id');
        });

        Schema::rename('grados_escolares', 'niveles');

        Schema::table('alumnos', function (Blueprint $table) {
            $table->foreign('nivel_id')->references('id')->on('niveles')->onDelete('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }
};