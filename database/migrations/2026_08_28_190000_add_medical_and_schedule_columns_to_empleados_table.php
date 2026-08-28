<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->string('horario')->nullable()->after('puesto');
            $table->date('fecha_nacimiento')->nullable()->after('horario');
            $table->text('numeros_emergencia')->nullable()->after('fecha_nacimiento');
            $table->string('tipo_sangre', 10)->nullable()->after('numeros_emergencia');
            $table->text('enfermedad')->nullable()->after('tipo_sangre');
            $table->text('alergias')->nullable()->after('enfermedad');
            $table->text('medicamento')->nullable()->after('alergias');
            $table->text('direccion')->nullable()->after('medicamento');
            $table->string('telefono_personal', 30)->nullable()->after('direccion');
            $table->string('curp', 18)->nullable()->after('telefono_personal');
        });
    }

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropColumn([
                'horario',
                'fecha_nacimiento',
                'numeros_emergencia',
                'tipo_sangre',
                'enfermedad',
                'alergias',
                'medicamento',
                'direccion',
                'telefono_personal',
                'curp',
            ]);
        });
    }
};
