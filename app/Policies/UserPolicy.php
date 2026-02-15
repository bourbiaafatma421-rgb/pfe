<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('MANAGER');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
         if ($authUser->role->nom === 'MANAGER' && $user->role->nom === 'rh') {
            return true;
        }

        // RH peut voir son propre profil
        if ($authUser->role->nom === 'rh' && $authUser->id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('MANAGER');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $authUser, User $user): bool
    {
        if ($authUser->role->nom === 'MANAGER' && $user->role->nom === 'rh') {
            return true;
        }

        if ($authUser->role->nom === 'rh' && $authUser->id === $user->id) {
            return true;
        }

        return false;
    }

    
    public function restore(User $user): bool
    {
        return false;
    }
}
