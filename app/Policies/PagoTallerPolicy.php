<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PagoTaller;
use App\Models\User;
use App\Policies\Concerns\HasRoleChecks;

final class PagoTallerPolicy
{
    use HasRoleChecks;

    public function view(User $user, PagoTaller $pagoTaller): bool
    {
        return $this->hasPaymentAccess($user)
            && $this->alumnoIsActive($pagoTaller->alumno);
    }

    public function update(User $user, PagoTaller $pagoTaller): bool
    {
        return $this->hasPaymentAccess($user)
            && $this->alumnoIsActive($pagoTaller->alumno);
    }

    public function delete(User $user, PagoTaller $pagoTaller): bool
    {
        return $this->hasPaymentAccess($user)
            && $this->alumnoIsActive($pagoTaller->alumno);
    }
}
