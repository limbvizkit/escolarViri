<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->foreignId('sucursal_id')->nullable()->after('grado_escolar_id')
                ->constrained('sucursales')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sucursal_id');
            $table->dropColumn('sucursal_id');
        });
    }
};
