<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Empleado extends Model
{
    use HasFactory;

    protected $fillable = [
        'sucursal_id',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'telefono',
        'puesto',
        'estatus',
    ];

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function usuario(): HasOne
    {
        return $this->hasOne(User::class);
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
                ->orWhere('email', 'like', $like)
                ->orWhere('telefono', 'like', $like)
                ->orWhere('puesto', 'like', $like);
        });
    }
}
