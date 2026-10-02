<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Adeudo;
use App\Models\AdeudoAbono;
use App\Models\Estatus;
use App\Models\User;
use App\Policies\Concerns\HasRoleChecks;

final class AdeudoPolicy
{
    use HasRoleChecks;

    public function view(User $user, Adeudo $adeudo): bool
    {
        return $this->hasDebtAccess($user)
            && $this->adeudoIsValid($adeudo);
    }

    public function update(User $user, Adeudo $adeudo): bool
    {
        return $this->hasDebtAccess($user)
            && $this->adeudoIsValid($adeudo);
    }

    public function delete(User $user, Adeudo $adeudo): bool
    {
        return $this->hasDebtAccess($user)
            && $this->adeudoIsValid($adeudo);
    }

    public function abonar(User $user, Adeudo $adeudo): bool
    {
        return $this->hasDebtAccess($user)
            && $this->adeudoIsValid($adeudo);
    }

    public function updateAbono(User $user, Adeudo $adeudo, AdeudoAbono $abono): bool
    {
        return $this->hasDebtAccess($user)
            && $this->adeudoIsValid($adeudo)
            && $abono->adeudo_id === $adeudo->id;
    }

    private function adeudoIsValid(Adeudo $adeudo): bool
    {
        return $adeudo->estatus_id === Estatus::ACTIVO
            && $this->alumnoIsActive($adeudo->alumno);
    }
}
