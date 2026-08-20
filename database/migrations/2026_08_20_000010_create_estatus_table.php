<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estatus', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        DB::table('estatus')->insert([
            ['nombre' => 'Activo', 'slug' => 'activo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Inactivo', 'slug' => 'inactivo', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Eliminado', 'slug' => 'eliminado', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('estatus');
    }
};
