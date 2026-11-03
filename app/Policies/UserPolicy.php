<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Allow administrators to access the user module.
     */
    public function viewAny(User $actor): bool
    {
        return (bool) $actor->is_admin;
    }

    /**
     * Restrict record visibility to the authorized company.
     */
    public function view(User $actor, User $target): bool
    {
        return $this->canManage($actor, $target);
    }

    /**
     * Allow administrators to create users.
     */
    public function create(User $actor): bool
    {
        return (bool) $actor->is_admin;
    }

    /**
     * Restrict editing to authorized users.
     */
    public function update(User $actor, User $target): bool
    {
        return $this->canManage($actor, $target);
    }

    /**
     * Temporarily disable deleting users.
     */
    public function delete(User $actor, User $target): bool
    {
        return false;
    }

    /**
     * Temporarily disable bulk deletion.
     */
    public function deleteAny(User $actor): bool
    {
        return false;
    }

    /**
     * Check company ownership or self-access.
     */
    private function canManage(User $actor, User $target): bool
    {
        if (! $actor->is_admin) {
            return false;
        }

        if ($actor->company_id === null) {
            return $actor->is($target);
        }

        return $target->company_id !== null
            && (int) $actor->company_id === (int) $target->company_id;
    }
}
