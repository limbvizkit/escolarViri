<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoLunch extends Model
{
    use HasFactory;

    protected $table = 'pagos_lunch';

    protected $fillable = [
        'alumno_id',
        'mes',
        'monto',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public function getMesLabelAttribute(): string
    {
        return Pago::mesLabel($this->mes);
    }

    public function scopeSearch($query, ?string $busqueda)
    {
        if ($busqueda === null || trim($busqueda) === '') {
            return $query;
        }

        $like = '%'.trim($busqueda).'%';

        return $query->whereHas('alumno', function ($q) use ($like) {
            $q->where('nombre', 'like', $like)
                ->orWhere('apellido_paterno', 'like', $like)
                ->orWhere('apellido_materno', 'like', $like);
        });
    }
}
