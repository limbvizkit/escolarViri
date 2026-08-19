<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TallerAlumno extends Model
{
    use HasFactory;

    protected $table = 'taller_alumno';

    protected $fillable = [
        'taller_id',
        'alumno_id',
        'hora_inicio',
        'hora_fin',
        'monto_pagado',
    ];

    protected function casts(): array
    {
        return [
            'monto_pagado' => 'decimal:2',
        ];
    }

    public function taller(): BelongsTo
    {
        return $this->belongsTo(Taller::class);
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }
}
