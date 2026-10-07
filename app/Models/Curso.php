<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Curso extends Model
{
    use HasFactory;

    protected $table = 'cursos';

    protected $fillable = [
        'nombre',
        'costo',
        'fecha_inicio',
        'fecha_fin',
        'hora_inicio',
        'hora_fin',
    ];

    protected function casts(): array
    {
        return [
            'costo' => 'decimal:2',
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    public function alumnos(): BelongsToMany
    {
        return $this->belongsToMany(Alumno::class, 'curso_alumno')->withTimestamps();
    }

    public function getPeriodoLabelAttribute(): string
    {
        $inicio = $this->fecha_inicio?->format('d/m/Y');
        $fin = $this->fecha_fin?->format('d/m/Y');

        if ($inicio && $fin) {
            return $inicio.' - '.$fin;
        }

        return $inicio ?? $fin ?? '—';
    }

    public function getHorarioLabelAttribute(): string
    {
        $inicio = substr((string) $this->hora_inicio, 0, 5);
        $fin = substr((string) $this->hora_fin, 0, 5);

        if ($this->hora_inicio && $this->hora_fin) {
            return $inicio.' - '.$fin;
        }

        return $this->hora_inicio ? $inicio : ($this->hora_fin ? $fin : '—');
    }
}
