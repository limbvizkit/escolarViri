<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Escuela extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'clave',
        'direccion',
        'telefono',
        'email',
        'estatus',
    ];

    public function sucursales(): HasMany
    {
        return $this->hasMany(Sucursal::class);
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
            $q->where('nombre', 'like', $like)
                ->orWhere('clave', 'like', $like)
                ->orWhere('direccion', 'like', $like)
                ->orWhere('telefono', 'like', $like)
                ->orWhere('email', 'like', $like);
        });
    }
}
