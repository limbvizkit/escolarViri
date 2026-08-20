<?php

namespace App\Models;

use App\Models\Concerns\ConEstatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormaPago extends Model
{
    use ConEstatus;
    use HasFactory;

    protected $table = 'formas_pago';

    protected $fillable = [
        'nombre',
        'estatus_id',
    ];

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function scopeSearch($query, ?string $busqueda)
    {
        if ($busqueda === null || trim($busqueda) === '') {
            return $query;
        }

        return $query->where('nombre', 'like', '%'.trim($busqueda).'%');
    }
}
