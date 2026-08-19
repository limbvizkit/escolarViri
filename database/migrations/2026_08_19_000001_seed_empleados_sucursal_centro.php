<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Empleados iniciales de la Sucursal Centro (id 1).
     * Sin email ni telefono, como se solicito.
     */
    private function empleados(): array
    {
        return [
            // nombre, apellido_paterno, apellido_materno, puesto
            ['Paulina', 'Pulido', 'Cecaldi', 'MISS'],
            ['Lorena', 'Perez', 'Lemmen Meyer', 'MISS'],
            ['Patricia', 'Prado', 'Garcia', 'COORDINADORA'],
            ['Andrea Viridiana', 'Moreno', 'Nateras', 'MISS'],
            ['Regina', '', null, 'ASISTENTE'],
            ['Brenda Osiris', 'Morales', 'Torres', 'MISS'],
            ['Itzel Marilin', 'Gómez', 'Martinez', 'ASISTENTE'],
            ['Marisol', 'Quiroz', 'Villanueva', 'MISS'],
            ['Leslie', 'Flores', 'Meza', 'ASISTENTE'],
            ['Daniela Michell', 'Godinez', 'Belmont', 'MISS'],
            ['Aidee', 'Garcia', 'Vega', 'ASISTENTE'],
            ['Frida Sarahi', 'Pacheco', 'Musiño', 'MISS'],
            ['Blanca Rocio', 'Pineda', 'Perez', 'ASISTENTE'],
            ['Maria Eloina', 'Carriles', 'Bretón', 'MISS'],
            ['Veronica Alejandra', 'Cordero', 'Palacios', 'MISS'],
            ['Leslie Mariana', 'Cruz', 'Garcia', 'ASISTENTE'],
        ];
    }

    public function up(): void
    {
        $rows = array_map(fn (array $e) => [
            'sucursal_id' => 1,
            'nombre' => $e[0],
            'apellido_paterno' => $e[1],
            'apellido_materno' => $e[2],
            'email' => null,
            'telefono' => null,
            'puesto' => $e[3],
            'estatus' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ], $this->empleados());

        DB::table('empleados')->insert($rows);
    }

    public function down(): void
    {
        foreach ($this->empleados() as [$nombre, $apellidoPaterno, $apellidoMaterno]) {
            DB::table('empleados')
                ->where('sucursal_id', 1)
                ->where('nombre', $nombre)
                ->where('apellido_paterno', $apellidoPaterno)
                ->where('apellido_materno', $apellidoMaterno)
                ->whereNull('email')
                ->whereNull('telefono')
                ->delete();
        }
    }
};
