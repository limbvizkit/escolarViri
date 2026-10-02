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
        return in_array($user->rol?->slug, ['admin', 'super-admin', 'director', 'recepcion'], true);
    }

    private function hasPaymentAccess(User $user): bool
    {
        return in_array($user->rol?->slug, ['admin', 'super-admin', 'director', 'recepcion'], true);
    }

    private function hasDebtAccess(User $user): bool
    {
        return in_array($user->rol?->slug, ['admin', 'super-admin', 'director', 'recepcion'], true);
    }

    private function hasWorkshopAccess(User $user): bool
    {
        return in_array($user->rol?->slug, ['admin', 'super-admin', 'director'], true);
    }

    private function alumnoIsActive(?Alumno $alumno): bool
    {
        return $alumno !== null && $alumno->estatus_id === Estatus::ACTIVO;
    }

    private function isUserManager(User $user): bool
    {
        return in_array($user->rol?->slug, ['admin', 'super-admin'], true);
    }

    private function isSuperAdmin(User $user): bool
    {
        return $user->rol?->slug === 'super-admin';
    }
}
