<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Rol;
use App\Models\User;
use App\Policies\Concerns\HasRoleChecks;

final class UserPolicy
{
    use HasRoleChecks;

    public function viewAny(User $actor): bool
    {
        return $this->isUserManager($actor);
    }

    public function create(User $actor): bool
    {
        return $this->isUserManager($actor);
    }

    public function view(User $actor, User $target): bool
    {
        return $this->isUserManager($actor);
    }

    public function update(User $actor, User $target): bool
    {
        if (! $this->isUserManager($actor)) {
            return false;
        }

        if ($this->isSuperAdmin($target)) {
            return $this->isSuperAdmin($actor);
        }

        return true;
    }

    public function delete(User $actor, User $target): bool
    {
        if ($actor->is($target)) {
            return false;
        }

        if (! $this->isUserManager($actor)) {
            return false;
        }

        if ($this->isSuperAdmin($target)) {
            return $this->isSuperAdmin($actor);
        }

        return true;
    }

    public function assignRole(User $actor, Rol $rol): bool
    {
        if ($rol->slug === 'super-admin') {
            return $this->isSuperAdmin($actor);
        }

        return $this->isUserManager($actor);
    }
}
