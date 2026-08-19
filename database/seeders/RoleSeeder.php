<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre' => 'Super Administrador', 'slug' => 'super-admin'],
            ['nombre' => 'Administrador', 'slug' => 'admin'],
            ['nombre' => 'Director', 'slug' => 'director'],
            ['nombre' => 'Profesor', 'slug' => 'profesor'],
            ['nombre' => 'Recepción', 'slug' => 'recepcion'],
        ];

        foreach ($roles as $rol) {
            Rol::updateOrCreate(['slug' => $rol['slug']], $rol);
        }
    }
}
