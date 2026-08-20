<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Adeudo extends Model
{
    use HasFactory;

    public const ESTATUS_PENDIENTE = 'pendiente';

    public const ESTATUS_PARCIAL = 'parcial';

    public const ESTATUS_PAGADO = 'pagado';

    protected $table = 'adeudos';

    protected $fillable = [
        'alumno_id',
        'concepto',
        'anotaciones',
        'monto',
        'monto_pagado',
        'estatus',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'monto_pagado' => 'decimal:2',
            'estatus' => 'string',
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public function abonos(): HasMany
    {
        return $this->hasMany(AdeudoAbono::class);
    }

    public function getPendienteAttribute(): float
    {
        return max(0.0, (float) $this->monto - (float) $this->monto_pagado);
    }

    public function getPendienteFormateadoAttribute(): string
    {
        return '$'.number_format($this->pendiente, 2);
    }

    public function scopeSearch($query, ?string $busqueda)
    {
        if ($busqueda === null || trim($busqueda) === '') {
            return $query;
        }

        $like = '%'.trim($busqueda).'%';

        return $query->where(function ($q) use ($like) {
            $q->where('concepto', 'like', $like)
                ->orWhereHas('alumno', function ($alumno) use ($like) {
                    $alumno->where('nombre', 'like', $like)
                        ->orWhere('apellido_paterno', 'like', $like);
                });
        });
    }
}
