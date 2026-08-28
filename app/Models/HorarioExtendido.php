<?php

namespace App\Models;

use App\Models\Concerns\ConEstatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HorarioExtendido extends Model
{
    use ConEstatus;
    use HasFactory;

    protected $table = 'horarios_extendidos';

    protected $fillable = [
        'nombre',
        'estatus_id',
    ];

    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class);
    }

    public function scopeSearch($query, ?string $busqueda)
    {
        if ($busqueda === null || trim($busqueda) === '') {
            return $query;
        }

        return $query->where('nombre', 'like', '%'.trim($busqueda).'%');
    }
}
