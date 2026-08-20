<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdeudoAbono extends Model
{
    use HasFactory;

    protected $table = 'adeudo_abonos';

    protected $fillable = [
        'adeudo_id',
        'forma_pago_id',
        'monto',
        'fecha',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha' => 'date',
        ];
    }

    public function adeudo(): BelongsTo
    {
        return $this->belongsTo(Adeudo::class);
    }

    public function formaPago(): BelongsTo
    {
        return $this->belongsTo(FormaPago::class);
    }
}
