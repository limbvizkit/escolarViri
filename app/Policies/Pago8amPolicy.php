<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Pago8am;
use App\Models\User;
use App\Policies\Concerns\HasRoleChecks;

final class Pago8amPolicy
{
    use HasRoleChecks;

    public function view(User $user, Pago8am $pago8am): bool
    {
        return $this->hasPaymentAccess($user)
            && $this->alumnoIsActive($pago8am->alumno);
    }

    public function update(User $user, Pago8am $pago8am): bool
    {
        return $this->hasPaymentAccess($user)
            && $this->alumnoIsActive($pago8am->alumno);
    }

    public function delete(User $user, Pago8am $pago8am): bool
    {
        return $this->hasPaymentAccess($user)
            && $this->alumnoIsActive($pago8am->alumno);
    }
}
