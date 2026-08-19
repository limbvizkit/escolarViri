<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    use HasFactory;

    protected $fillable = [
        'alumno_id',
        'mes',
        'fecha',
        'entrada_8am',
        'pronto_pago',
        'pago_normal',
        'talleres',
        'forma_pago_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'entrada_8am' => 'decimal:2',
            'pronto_pago' => 'decimal:2',
            'pago_normal' => 'decimal:2',
            'talleres' => 'decimal:2',
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public function formaPago(): BelongsTo
    {
        return $this->belongsTo(FormaPago::class);
    }

    public static function mesLabel(?string $mes): string
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        [$anio, $numMes] = array_pad(explode('-', (string) $mes), 2, '');

        if ($numMes === '' || ! isset($meses[(int) $numMes])) {
            return trim((string) $mes);
        }

        return $meses[(int) $numMes].' '.$anio;
    }

    public function getMesLabelAttribute(): string
    {
        return static::mesLabel($this->mes);
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
