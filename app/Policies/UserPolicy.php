<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * $adminUser is the currently logged-in Company Admin.
     * $targetUser is the user they are trying to view/edit/delete.
     */
    public function view(User $adminUser, User $targetUser): bool
    {
        // Allow the action ONLY if the admin's company_id
        // is the same as the target user's company_id.
        return $adminUser->company_id === $targetUser->company_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $adminUser, User $targetUser): bool
    {
        // Use the same logic for updating.
        return $adminUser->company_id === $targetUser->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $adminUser, User $targetUser): bool
    {
        // Use the same logic for deleting.
        return $adminUser->company_id === $targetUser->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // We'll leave this as true or empty, as the 'store' method
        // in the controller handles the logic of who can create.
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $adminUser, User $targetUser): bool
    {
        // Not using this for now
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $adminUser, User $targetUser): bool
    {
        // Not using this for now
        return false;
    }
}
