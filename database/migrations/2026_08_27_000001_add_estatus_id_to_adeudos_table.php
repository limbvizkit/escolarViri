<?php

use App\Models\Estatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adeudos', function (Blueprint $table) {
            $table->foreignId('estatus_id')->default(Estatus::ACTIVO)->after('estatus')->constrained('estatus');
        });
    }

    public function down(): void
    {
        Schema::table('adeudos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('estatus_id');
        });
    }
};
