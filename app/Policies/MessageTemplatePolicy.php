<?php

namespace App\Policies;

use App\Models\MessageTemplate;
use App\Models\User;

class MessageTemplatePolicy
{
    /**
     * Allow administrators to access the templates module.
     */
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    /**
     * Restrict template visibility to authorized companies.
     */
    public function view(User $user, MessageTemplate $template): bool
    {
        return $this->canManage($user, $template);
    }

    /**
     * Allow administrators to create templates.
     */
    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    /**
     * Restrict editing to authorized templates.
     */
    public function update(User $user, MessageTemplate $template): bool
    {
        return $this->canManage($user, $template);
    }

    /**
     * Temporarily disable template deletion.
     */
    public function delete(User $user, MessageTemplate $template): bool
    {
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
     * Check whether the template belongs to the user's company.
     */
    private function canManage(
        User $user,
        MessageTemplate $template
    ): bool {
        if (! $user->is_admin) {
            return false;
        }

        // Temporary platform administrator.
        if ($user->company_id === null) {
            return true;
        }

        return $template->company_id !== null
            && (int) $user->company_id === (int) $template->company_id;
    }
}
