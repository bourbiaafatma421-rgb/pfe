<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;

class RolePolicy
{
    /**
     * Détermine si l'utilisateur peut voir tous les rôles.
     */
    public function viewAny(User $user): bool
    {
        //Seul le RH peut voir la liste des rôles
        return $user->hasRole('rh');
    }
    /**
     * Détermine si l'utilisateur peut créer un rôle.
     */
    public function create(User $user): bool
    {
        // Seul le RH peut créer un rôle
        return $user->hasRole('rh');
    }
    /**
     * Détermine si l'utilisateur peut modifier un rôle.
     */
    public function update(User $user, Role $role): bool
    {
        // Seul le RH peut modifier
        return $user->hasRole('rh');
    }

    /**
     * Détermine si l'utilisateur peut supprimer un rôle.
     */
    public function delete(User $user, Role $role): bool
    {
        // Seul le RH peut supprimer
        return $user->hasRole('rh');
    }

    /**
     * Facultatif : restauration ou suppression définitive
     */
    public function restore(User $user, Role $role): bool
    {
        return false;
    }

    public function forceDelete(User $user, Role $role): bool
    {
        return false;
    }
}
