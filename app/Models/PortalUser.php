<?php

namespace App\Models;

use Database\Factories\PortalUserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable([
    'name',
    'email',
    'phone',
    'alumno_nombre',
    'grado_escolar_id',
    'password',
    'must_change_password',
])]
#[Hidden(['password', 'remember_token'])]
class PortalUser extends Authenticatable
{
    /** @use HasFactory<PortalUserFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'must_change_password' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Normalize the email to lowercase before persisting it, so accounts that
     * only differ by letter case cannot coexist.
     */
    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => $value === null ? null : strtolower(trim($value)),
        );
    }

    public function gradoEscolar(): BelongsTo
    {
        return $this->belongsTo(GradoEscolar::class);
    }
}
