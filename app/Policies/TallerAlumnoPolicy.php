<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TallerAlumno;
use App\Models\User;
use App\Policies\Concerns\HasRoleChecks;

final class TallerAlumnoPolicy
{
    use HasRoleChecks;

    public function update(User $user, TallerAlumno $tallerAlumno): bool
    {
        return $this->hasWorkshopAccess($user)
            && $this->alumnoIsActive($tallerAlumno->alumno);
    }
}
