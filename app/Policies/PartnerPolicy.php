<?php

namespace App\Policies;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PartnerPolicy
{
    /**
     * Determine whether the user can view any models.
     * (Not needed for admin, public index exists)
     */
    // public function viewAny(User $user): bool { return true; }

    /**
     * Determine whether the user can view the model.
     * (Not needed for admin, public show exists)
     */
    // public function view(User $user, Partner $partner): bool { return true; }

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
    public function update(User $user, Partner $partner): bool
    {
        return $user->role === 'super_admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Partner $partner): bool
    {
        return $user->role === 'super_admin';
    }

    // We can leave restore/forceDelete empty for now.
}
