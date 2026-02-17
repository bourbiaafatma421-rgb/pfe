<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
<<<<<<< HEAD
    public function viewAny(User $user): bool
    {
        return $user->role && in_array(strtoupper($user->role->name), ['MANAGER']);
    }

    public function view(User $user, User $model)
    {
       
        if ($user->role && in_array(strtoupper($user->role->name), ['rh', 'MANAGER'])) {
            return true;
        }

        if ($user->id === $model->id) {
=======
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

    /**
     * Détermine si l'utilisateur peut voir un utilisateur spécifique
     */
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
>>>>>>> origin/main
            return true;
        }

        return false;
    }

<<<<<<< HEAD
    public function update(User $currentUser, User $targetUser)
{
    

    // RH peut modifier tous les profils
    if ($currentUser->role && strtoupper($currentUser->role->name) === 'RH') {
        return true;
=======
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
>>>>>>> origin/main
    }

    // Manager peut aussi modifier les profils
    if ($currentUser->role && strtoupper($currentUser->role->name) === 'MANAGER') {
        return true;
    }

    // Un utilisateur peut modifier son propre profil
    if ($currentUser->id === $targetUser->id) {
        return true;
    }

    // Sinon accès refusé
    return false;
}

    // ===== NOUVELLES MÉTHODES =====

    public function create(User $user)
    {
        return $user->role && strtoupper($user->role->name) === 'MANAGER';
    }

    /*public function delete(User $user, User $model)
    {
        if ($user->id === $model->id) return false;

        return $user->role && in_array(strtoupper($user->role->nom), ['rh', 'MANAGER']);
    }*/

    public function restore(User $user, User $model)
    {
        return $user->role && strtoupper($user->role->name) === 'MANAGER';
    }


    public function toggleActive(User $currentUser, User $targetUser)
{
    if (in_array(strtoupper($currentUser->role->name ?? ''), [ 'MANAGER'])) {
        return true;
    }

    return false; 
}
}
