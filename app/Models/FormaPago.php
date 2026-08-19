<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormaPago extends Model
{
    use HasFactory;

    protected $table = 'formas_pago';

    protected $fillable = [
        'nombre',
        'estatus',
    ];

    protected function casts(): array
    {
        return [
            'estatus' => 'boolean',
        ];
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
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
