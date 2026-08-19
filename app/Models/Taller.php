<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Taller extends Model
{
    use HasFactory;

    protected $table = 'talleres';

    protected $fillable = [
        'nombre',
        'costo',
    ];

    protected function casts(): array
    {
        return [
            'costo' => 'decimal:2',
        ];
    }

    public function alumnos(): BelongsToMany
    {
        return $this->belongsToMany(Alumno::class, 'taller_alumno')
            ->withPivot('hora_inicio', 'hora_fin', 'monto_pagado')
            ->withTimestamps();
    }
}
