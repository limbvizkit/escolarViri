<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Documento;
use App\Models\User;
use App\Policies\Concerns\HasRoleChecks;

final class DocumentoPolicy
{
    use HasRoleChecks;

    public function view(User $user, Documento $documento): bool
    {
        return $this->hasDocumentAccess($user)
            && $this->alumnoIsActive($documento->alumno);
    }

    public function delete(User $user, Documento $documento): bool
    {
        return $this->hasDocumentAccess($user)
            && $this->alumnoIsActive($documento->alumno);
    }
}
