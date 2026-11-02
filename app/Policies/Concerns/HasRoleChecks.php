<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use App\Models\Alumno;
use App\Models\Estatus;
use App\Models\User;

trait HasRoleChecks
{
    private function hasDocumentAccess(User $user): bool
    {
        return $user->puedeVer('documentacion');
    }

    private function hasPaymentAccess(User $user): bool
    {
        return $user->puedeVer('pagos');
    }

    private function hasDebtAccess(User $user): bool
    {
        return $user->puedeVer('adeudos');
    }

    private function hasWorkshopAccess(User $user): bool
    {
        return $user->puedeVer('talleres');
    }

    private function alumnoIsActive(?Alumno $alumno): bool
    {
        return $alumno !== null && $alumno->estatus_id === Estatus::ACTIVO;
    }

    private function isUserManager(User $user): bool
    {
        return $user->puedeVer('usuarios');
    }

    private function isSuperAdmin(User $user): bool
    {
        return $user->esSuperAdmin();
    }
}
