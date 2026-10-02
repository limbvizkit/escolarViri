<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoOnline extends Model
{
    use HasFactory;

    public const string STATUS_PENDING = 'pending';

    public const string STATUS_COMPLETED = 'completed';

    public const string STATUS_FAILED = 'failed';

    protected $table = 'pagos_online';

    protected $fillable = [
        'portal_user_id',
        'concepto',
        'monto',
        'moneda',
        'order_id',
        'openpay_charge_id',
        'estatus',
        'authorization',
        'card_brand',
        'card_last4',
        'error_code',
        'error_category',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'moneda' => 'string',
            'estatus' => 'string',
        ];
    }

    public function portalUser(): BelongsTo
    {
        return $this->belongsTo(PortalUser::class);
    }

    public function scopeForPortalUser($query, PortalUser $portalUser)
    {
        return $query->where('portal_user_id', $portalUser->id);
    }

    public function scopeSearch($query, ?string $busqueda)
    {
        if ($busqueda === null || trim($busqueda) === '') {
            return $query;
        }

        $like = '%'.trim($busqueda).'%';

        return $query->where(function ($q) use ($like) {
            $q->where('concepto', 'like', $like)
                ->orWhere('order_id', 'like', $like)
                ->orWhere('openpay_charge_id', 'like', $like)
                ->orWhereHas('portalUser', function ($user) use ($like) {
                    $user->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
        });
    }

    public function isPending(): bool
    {
        return $this->estatus === self::STATUS_PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->estatus === self::STATUS_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->estatus === self::STATUS_FAILED;
    }

    public function statusLabel(): string
    {
        return match ($this->estatus) {
            self::STATUS_COMPLETED => 'Completado',
            self::STATUS_FAILED => 'Fallido',
            self::STATUS_PENDING => 'Pendiente',
            default => ucfirst($this->estatus),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->estatus) {
            self::STATUS_COMPLETED => 'ip-badge-active',
            self::STATUS_FAILED => 'bg-danger',
            self::STATUS_PENDING => 'bg-warning text-dark',
            default => 'bg-secondary',
        };
    }
}
