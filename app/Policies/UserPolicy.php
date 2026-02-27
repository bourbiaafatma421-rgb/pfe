<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Détermine si l'utilisateur peut voir tous les utilisateurs
     */
    public function viewAny(User $user): bool
    {
        // Manager peut voir tous les staffs
        if ($user->hasRole('manager')) {
            return true;
        }

        // RH peut voir tous les collaborateurs
        if ($user->hasRole('rh')) {
            return true;
        }

        return false;
    }

 
     //Détermine si l'utilisateur peut voir un utilisateur spécifique
    public function view(User $user, User $target): bool
    {
        // Manager peut voir tous les staffs
        if ($user->hasRole('manager')) {
            return true;
        }

        // RH peut voir tous les collaborateurs
        if ($user->hasRole('rh')) {
            return true;
        }

        // Collaborateur peut voir seulement son propre profil
        return $user->id === $target->id;
    }

    /**
     * Détermine si l'utilisateur peut créer un utilisateur
     */
    public function create(User $user): bool
    {
        // Manager peut créer des staffs
        if ($user->hasRole('manager')) {
            return true;
        }

        // RH peut créer des collaborateurs
        if ($user->hasRole('rh')) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour un utilisateur
     */
    public function update(User $user, User $target): bool
    {
        // Manager peut modifier tous les staffs
        if ($user->hasRole('manager')) {
            return true;
        }

        // RH peut modifier tous les collaborateurs
        if ($user->hasRole('rh')) {
            return true;
        }

        // Collaborateur peut modifier uniquement son propre profil
        if ($user->hasRole('new_collaborateur') && $user->id === $target->id) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut supprimer un utilisateur
     */
    public function delete(User $user, User $target): bool
    {
        // Pour l'instant, personne ne peut supprimer
        return false;
    }

    /**
     * Détermine si l'utilisateur peut restaurer un utilisateur
     */
    public function restore(User $user, User $target): bool
    {
        return false;
    }

    /**
     * Détermine si l'utilisateur peut supprimer définitivement un utilisateur
     */
    public function forceDelete(User $user, User $target): bool
    {
        return false;
    }
}
