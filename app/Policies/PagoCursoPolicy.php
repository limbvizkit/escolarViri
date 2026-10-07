<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PagoCurso;
use App\Models\User;
use App\Policies\Concerns\HasRoleChecks;

final class PagoCursoPolicy
{
    use HasRoleChecks;

    public function view(User $user, PagoCurso $pagoCurso): bool
    {
        return $this->hasPaymentAccess($user)
            && $this->alumnoIsActive($pagoCurso->alumno);
    }

    public function update(User $user, PagoCurso $pagoCurso): bool
    {
        return $this->hasPaymentAccess($user)
            && $this->alumnoIsActive($pagoCurso->alumno);
    }

    public function delete(User $user, PagoCurso $pagoCurso): bool
    {
        return $this->hasPaymentAccess($user)
            && $this->alumnoIsActive($pagoCurso->alumno);
    }
}
