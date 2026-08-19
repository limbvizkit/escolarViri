<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradoEscolar extends Model
{
    use HasFactory;

    protected $table = 'grados_escolares';

    protected $fillable = [
        'nombre',
        'slug',
        'estatus',
    ];

    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class);
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

        return $query->where('nombre', 'like', '%'.trim($busqueda).'%');
    }
}
