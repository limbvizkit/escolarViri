<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PagoLunch;
use App\Models\User;
use App\Policies\Concerns\HasRoleChecks;

final class PagoLunchPolicy
{
    use HasRoleChecks;

    public function view(User $user, PagoLunch $pagoLunch): bool
    {
        return $this->hasPaymentAccess($user)
            && $this->alumnoIsActive($pagoLunch->alumno);
    }

    public function update(User $user, PagoLunch $pagoLunch): bool
    {
        return $this->hasPaymentAccess($user)
            && $this->alumnoIsActive($pagoLunch->alumno);
    }

    public function delete(User $user, PagoLunch $pagoLunch): bool
    {
        return $this->hasPaymentAccess($user)
            && $this->alumnoIsActive($pagoLunch->alumno);
    }
}
