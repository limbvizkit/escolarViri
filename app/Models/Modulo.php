<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modulo extends Model
{
    protected $table = 'modulos';

    protected $fillable = [
        'clave',
        'nombre',
        'grupo',
        'orden',
        'prefijo',
        'ruta',
        'url',
    ];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
        ];
    }

    public function permisos(): HasMany
    {
        return $this->hasMany(Permiso::class, 'modulo_id');
    }

    /**
     * `orden` is global and grouped contiguously per `grupo`, so this keeps
     * every group together in catalog order without a second sort key.
     */
    public function scopeOrdenado(Builder $query): Builder
    {
        return $query->orderBy('orden');
    }
}
