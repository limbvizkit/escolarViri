<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DatoFacturacion;
use App\Models\User;
use App\Policies\Concerns\HasRoleChecks;

final class DatoFacturacionPolicy
{
    use HasRoleChecks;

    public function view(User $user, DatoFacturacion $datoFacturacion): bool
    {
        return $this->hasPaymentAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->hasPaymentAccess($user);
    }

    public function delete(User $user, DatoFacturacion $datoFacturacion): bool
    {
        return $this->hasPaymentAccess($user);
    }
}
