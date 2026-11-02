<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modulos', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->string('nombre');
            $table->string('grupo');
            $table->unsignedInteger('orden')->default(0);
            $table->string('prefijo')->unique();
            $table->string('ruta');
            $table->string('url');
            $table->timestamps();
        });

        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('modulo_id')->constrained('modulos')->cascadeOnDelete();
            $table->boolean('puede_ver')->default(false);
            $table->boolean('puede_escribir')->default(false);
            $table->boolean('puede_eliminar')->default(false);
            $table->timestamps();

            $table->unique(['rol_id', 'modulo_id']);
        });

        $this->backfill();
    }

    /**
     * Seed the catalog and the initial matrix. Plain queries on purpose: this
     * runs before the application models may be deployed, and it must be safe
     * to run more than once.
     */
    private function backfill(): void
    {
        $now = now();

        foreach (config('modulos.modulos', []) as $modulo) {
            $atributos = [
                'nombre' => $modulo['nombre'],
                'grupo' => $modulo['grupo'],
                'orden' => $modulo['orden'],
                'prefijo' => $modulo['prefijo'],
                'ruta' => $modulo['ruta'],
                'url' => $modulo['url'],
            ];

            $existe = DB::table('modulos')->where('clave', $modulo['clave'])->exists();

            if ($existe) {
                DB::table('modulos')->where('clave', $modulo['clave'])->update($atributos + ['updated_at' => $now]);
            } else {
                DB::table('modulos')->insert($atributos + [
                    'clave' => $modulo['clave'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $modulos = DB::table('modulos')->pluck('id', 'clave')->all();
        $roles = DB::table('roles')->pluck('id', 'slug')->all();

        if ($roles === []) {
            return;
        }

        foreach (config('modulos.inicial', []) as $clave => $acciones) {
            if (! isset($modulos[$clave])) {
                continue;
            }

            foreach ($roles as $slug => $rolId) {
                if ($slug === 'super-admin') {
                    continue;
                }

                DB::table('permisos')->insertOrIgnore([
                    'rol_id' => $rolId,
                    'modulo_id' => $modulos[$clave],
                    'puede_ver' => in_array($slug, $acciones['ver'] ?? [], true),
                    'puede_escribir' => in_array($slug, $acciones['escribir'] ?? [], true),
                    'puede_eliminar' => in_array($slug, $acciones['eliminar'] ?? [], true),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos');
        Schema::dropIfExists('modulos');
    }
};
