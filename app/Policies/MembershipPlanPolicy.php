<?php

namespace App\Policies;

use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MembershipPlanPolicy
{
    /**
     * Determine whether the user can view the model.
     * (This is for the admin-only 'show' route)
     */
    public function view(User $user, MembershipPlan $membershipPlan): bool
    {
        // Only a super admin can view the details
        return $user->role === 'super_admin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'super_admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MembershipPlan $membershipPlan): bool
    {
        return $user->role === 'super_admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MembershipPlan $membershipPlan): bool
    {
        return $user->role === 'super_admin';
    }

    // --- We can delete the methods we aren't using ---
    // (Or you can leave them as 'return false;', both are fine)

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // This is for the public index, so we can allow it,
        // but our controller doesn't check this policy for the index.
        return true; 
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MembershipPlan $membershipPlan): bool
    {
        return $user->role === 'super_admin'; // Good to have for soft deletes
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MembershipPlan $membershipPlan): bool
    {
        return $user->role === 'super_admin'; // Good to have
    }
}