<?php

namespace App\Policies;

use App\Models\MessageAutomation;
use App\Models\User;

class MessageAutomationPolicy
{
    /**
     * Allow administrators to access the reminders module.
     */
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    /**
     * Restrict reminder visibility to the authorized company.
     */
    public function view(
        User $user,
        MessageAutomation $reminder
    ): bool {
        return $this->canManage($user, $reminder);
    }

    /**
     * Allow administrators to create reminders.
     */
    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    /**
     * Restrict editing to reminders of the same company.
     */
    public function update(
        User $user,
        MessageAutomation $reminder
    ): bool {
        return $this->canManage($user, $reminder);
    }

    /**
     * Temporarily disable reminder deletion.
     */
    public function delete(
        User $user,
        MessageAutomation $reminder
    ): bool {
        return false;
    }

    /**
     * Temporarily disable bulk deletion.
     */
    public function deleteAny(User $user): bool
    {
        return false;
    }

    /**
     * Check company ownership.
     */
    private function canManage(
        User $user,
        MessageAutomation $reminder
    ): bool {
        if (! $user->is_admin) {
            return false;
        }

        // Temporary platform administrator.
        if ($user->company_id === null) {
            return true;
        }

        return $reminder->company_id !== null
            && (int) $user->company_id === (int) $reminder->company_id;
    }
}
