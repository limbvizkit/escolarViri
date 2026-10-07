<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PagoHorarioExtendido;
use App\Models\User;
use App\Policies\Concerns\HasRoleChecks;

final class PagoHorarioExtendidoPolicy
{
    use HasRoleChecks;

    public function view(User $user, PagoHorarioExtendido $pagoHorarioExtendido): bool
    {
        return $this->hasPaymentAccess($user)
            && $this->alumnoIsActive($pagoHorarioExtendido->alumno);
    }

    public function update(User $user, PagoHorarioExtendido $pagoHorarioExtendido): bool
    {
        return $this->hasPaymentAccess($user)
            && $this->alumnoIsActive($pagoHorarioExtendido->alumno);
    }

    public function delete(User $user, PagoHorarioExtendido $pagoHorarioExtendido): bool
    {
        return $this->hasPaymentAccess($user)
            && $this->alumnoIsActive($pagoHorarioExtendido->alumno);
    }
}
