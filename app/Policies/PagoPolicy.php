<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Pago;
use App\Models\User;
use App\Policies\Concerns\HasRoleChecks;

final class PagoPolicy
{
    use HasRoleChecks;

    public function view(User $user, Pago $pago): bool
    {
        return $this->hasPaymentAccess($user)
            && $this->alumnoIsActive($pago->alumno);
    }

    public function update(User $user, Pago $pago): bool
    {
        return $this->hasPaymentAccess($user)
            && $this->alumnoIsActive($pago->alumno);
    }

    public function delete(User $user, Pago $pago): bool
    {
        return $this->hasPaymentAccess($user)
            && $this->alumnoIsActive($pago->alumno);
    }
}
