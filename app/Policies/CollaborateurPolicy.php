<?php

namespace App\Policies;

use App\Models\Collaborateur;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CollaborateurPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Collaborateur $collaborateur): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === 'rh';
    }

    /**
     * Determine whether the user can update the model.
     */
     public function update(User $user, Collaborateur $collaborateur)
    {
        // RH peut modifier tous les collaborateurs
        if ($user->role === 'rh') {
            return true;
        }

        // Collaborateur peut uniquement modifier SON numéro de téléphone
        if ($user->role === 'collaborateur' && $user->id === $collaborateur->user_id) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Collaborateur $collaborateur): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Collaborateur $collaborateur): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Collaborateur $collaborateur): bool
    {
        return false;
    }
}
