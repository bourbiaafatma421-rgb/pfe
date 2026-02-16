<?php

namespace App\Policies;

use App\Models\User;

class CollaborateurPolicy
{
    /**
     * Determine whether the user can view any collaborateurs.
     */
    public function viewAny(User $user): bool
    {
        // RH et Manager peuvent voir tous les collaborateurs
        return $user->hasRole('rh') || $user->hasRole('manager');
    }

    /**
     * Determine whether the user can view a specific collaborateur.
     */
    public function view(User $user, User $collaborateur): bool
    {
        // RH peut voir tous les collaborateurs
        if ($user->hasRole('rh')) {
            return true;
        }

        // Collaborateur peut voir seulement son propre profil
        return $user->id === $collaborateur->id;
    }

    /**
     * Determine whether the user can create collaborateurs.
     */
    public function create(User $user): bool
    {
        // Seul RH peut créer
        return $user->hasRole('rh');
    }

    /**
     * Determine whether the user can update a collaborateur.
     */
    public function update(User $user, User $collaborateur): bool
    {
        // RH peut modifier tous les collaborateurs
        if ($user->hasRole('rh')) {
            return true;
        }

        // Collaborateur peut uniquement modifier SON profil
        if ($user->hasRole('new_collaborateur') && $user->id === $collaborateur->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete a collaborateur.
     */
    public function delete(User $user, User $collaborateur): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore a collaborateur.
     */
    public function restore(User $user, User $collaborateur): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete a collaborateur.
     */
    public function forceDelete(User $user, User $collaborateur): bool
    {
        return false;
    }
}
