<?php

namespace App\Policies;

use App\Models\Person;
use App\Models\User;

class PersonPolicy
{
    /**
     * Allow administrators to access the clients module.
     */
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    /**
     * Restrict client visibility to the authorized company.
     */
    public function view(User $user, Person $person): bool
    {
        return $this->canManage($user, $person);
    }

    /**
     * Allow authorized administrators to create clients.
     */
    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    /**
     * Restrict editing to clients of the same company.
     */
    public function update(User $user, Person $person): bool
    {
        return $this->canManage($user, $person);
    }

    /**
     * Disable client deletion temporarily.
     */
    public function delete(User $user, Person $person): bool
    {
        return false;
    }

    /**
     * Disable bulk deletion.
     */
    public function deleteAny(User $user): bool
    {
        return false;
    }

    /**
     * Validate company ownership.
     */
    private function canManage(User $user, Person $person): bool
    {
        if (! $user->is_admin) {
            return false;
        }

        // Temporary platform administrator.
        if ($user->company_id === null) {
            return true;
        }

        return $person->company_id !== null
            && (int) $user->company_id === (int) $person->company_id;
    }
}
