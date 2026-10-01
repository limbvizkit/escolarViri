<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AcademicDocument;
use App\Models\User;
use App\Policies\Concerns\HasRoleChecks;

final class AcademicDocumentPolicy
{
    use HasRoleChecks;

    public function view(User $user, AcademicDocument $academicDocument): bool
    {
        return $this->hasDocumentAccess($user)
            && $this->alumnoIsActive($academicDocument->alumno);
    }

    public function update(User $user, AcademicDocument $academicDocument): bool
    {
        return $this->hasDocumentAccess($user)
            && $this->alumnoIsActive($academicDocument->alumno);
    }

    public function delete(User $user, AcademicDocument $academicDocument): bool
    {
        return $this->hasDocumentAccess($user)
            && $this->alumnoIsActive($academicDocument->alumno);
    }
}
