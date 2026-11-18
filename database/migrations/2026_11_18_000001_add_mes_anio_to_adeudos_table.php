<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adeudos', function (Blueprint $table) {
            $table->unsignedTinyInteger('mes')->nullable()->after('anotaciones');
            $table->unsignedSmallInteger('anio')->nullable()->after('mes');
        });
    }

    public function down(): void
    {
        Schema::table('adeudos', function (Blueprint $table) {
            $table->dropColumn(['mes', 'anio']);
        });
    }
};
