<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    /**
     * Allow administrators to view the company module.
     */
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    /**
     * Allow access only to authorized company records.
     */
    public function view(User $user, Company $company): bool
    {
        return $this->canManage($user, $company);
    }

    /**
     * Only the initial platform administrator can create companies.
     */
    public function create(User $user): bool
    {
        return $user->is_admin
            && $user->company_id === null;
    }

    /**
     * Restrict company modifications.
     */
    public function update(User $user, Company $company): bool
    {
        return $this->canManage($user, $company);
    }

    /**
     * Disable company deletion for now.
     */
    public function delete(User $user, Company $company): bool
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
     * Check whether the administrator can manage a company.
     */
    private function canManage(User $user, Company $company): bool
    {
        if (! $user->is_admin) {
            return false;
        }

        if ($user->company_id === null) {
            return true;
        }

        return (int) $user->company_id === (int) $company->id;
    }
}
