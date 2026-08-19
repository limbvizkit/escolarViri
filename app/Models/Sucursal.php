<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursales';

    protected $fillable = [
        'escuela_id',
        'nombre',
        'direccion',
        'telefono',
        'email',
        'estatus',
    ];

    public function escuela(): BelongsTo
    {
        return $this->belongsTo(Escuela::class);
    }

    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class);
    }

    public function scopeActive($query)
    {
        return $query->where('estatus', true);
    }

    public function scopeSearch($query, ?string $busqueda)
    {
        if ($busqueda === null || trim($busqueda) === '') {
            return $query;
        }

        $like = '%'.trim($busqueda).'%';

        return $query->where(function ($q) use ($like) {
            $q->where('sucursales.nombre', 'like', $like)
                ->orWhere('sucursales.direccion', 'like', $like)
                ->orWhere('sucursales.telefono', 'like', $like)
                ->orWhere('sucursales.email', 'like', $like)
                ->orWhereHas('escuela', fn ($e) => $e->where('nombre', 'like', $like));
        });
    }
}
