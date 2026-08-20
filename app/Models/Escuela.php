<?php

namespace App\Models;

use App\Models\Concerns\ConEstatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Escuela extends Model
{
    use ConEstatus;
    use HasFactory;

    protected $fillable = [
        'nombre',
        'clave',
        'direccion',
        'telefono',
        'email',
        'estatus_id',
    ];

    public function sucursales(): HasMany
    {
        return $this->hasMany(Sucursal::class);
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
