<?php

namespace App\Models\Concerns;

use App\Models\Estatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait ConEstatus
{
    public function estatus(): BelongsTo
    {
        return $this->belongsTo(Estatus::class);
    }

    public function scopeActive($query): Builder
    {
        return $query->where($this->getTable().'.estatus_id', Estatus::ACTIVO);
    }

    public function scopeVisibles($query): Builder
    {
        return $query->where($this->getTable().'.estatus_id', '!=', Estatus::ELIMINADO);
    }

    public function getEstatusEsActivoAttribute(): bool
    {
        return $this->estatus_id === Estatus::ACTIVO;
    }

    public function getEstatusNombreAttribute(): string
    {
        return $this->estatus->nombre ?? '—';
    }

    public function getEstatusBadgeAttribute(): string
    {
        return match ($this->estatus_id) {
            Estatus::ACTIVO => 'active',
            Estatus::INACTIVO => 'inactive',
            default => 'deleted',
        };
    }

    protected static function bootConEstatus(): void
    {
        static::addGlobalScope('no-eliminado', function (Builder $builder) {
            $builder->where($builder->getModel()->getTable().'.estatus_id', '!=', Estatus::ELIMINADO);
        });
    }
}
