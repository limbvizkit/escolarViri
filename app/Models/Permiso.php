<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permiso extends Model
{
    protected $table = 'permisos';

    protected $fillable = [
        'rol_id',
        'modulo_id',
        'puede_ver',
        'puede_escribir',
        'puede_eliminar',
    ];

    protected function casts(): array
    {
        return [
            'puede_ver' => 'boolean',
            'puede_escribir' => 'boolean',
            'puede_eliminar' => 'boolean',
        ];
    }

    /**
     * Writing or deleting implies reading: keep the matrix internally
     * consistent no matter who persists it.
     */
    protected static function booted(): void
    {
        static::saving(function (Permiso $permiso): void {
            if ($permiso->puede_escribir || $permiso->puede_eliminar) {
                $permiso->puede_ver = true;
            }
        });
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class, 'modulo_id');
    }
}
