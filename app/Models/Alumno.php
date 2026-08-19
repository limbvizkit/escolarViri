<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alumno extends Model
{
    use HasFactory;

    protected $fillable = [
        'grado_escolar_id',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'horario',
        'inscripcion',
        'reinscripcion',
        'entrevista_inicial',
        'nat_geo',
        'cuota_materiales',
        'fecha_ingreso',
        'cuota_mensual',
        'estatus',
        'archivo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_ingreso' => 'date',
            'inscripcion' => 'decimal:2',
            'reinscripcion' => 'decimal:2',
            'entrevista_inicial' => 'decimal:2',
            'nat_geo' => 'decimal:2',
            'cuota_materiales' => 'decimal:2',
            'cuota_mensual' => 'decimal:2',
        ];
    }

    public function gradoEscolar(): BelongsTo
    {
        return $this->belongsTo(GradoEscolar::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function talleres(): BelongsToMany
    {
        return $this->belongsToMany(Taller::class, 'taller_alumno')
            ->withPivot('hora_inicio', 'hora_fin', 'monto_pagado')
            ->withTimestamps();
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombre.' '.$this->apellido_paterno.' '.$this->apellido_materno);
    }

    public function scopeSearch($query, ?string $busqueda)
    {
        if ($busqueda === null || trim($busqueda) === '') {
            return $query;
        }

        $like = '%'.trim($busqueda).'%';

        return $query->where(function ($q) use ($like) {
            $q->where('nombre', 'like', $like)
                ->orWhere('apellido_paterno', 'like', $like)
                ->orWhere('apellido_materno', 'like', $like)
                ->orWhere('horario', 'like', $like);
        });
    }
}
