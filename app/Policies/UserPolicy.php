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
        if (!$user->role) return false;

        // Comparaison insensible à la casse
        return strcasecmp($user->role->name, 'rh') === 0
            || strcasecmp($user->role->name, 'MANAGER') === 0;
    }

    /**
     * Détermine si l'utilisateur peut voir un utilisateur spécifique
     */
    public function view(User $user, User $target): bool
    {
        // Manager ou RH peut voir n'importe quel utilisateur
        if ($user->role && in_array(strtoupper($user->role->name), ['rh', 'MANAGER'])) {
            return true;
        }

        // Un utilisateur peut voir son propre profil
        return $user->id === $target->id;
    }

    /**
     * Détermine si l'utilisateur peut créer un utilisateur
     */
    public function create(User $user): bool
    {
        if (!$user->role) return false;

        return strcasecmp($user->role->name, 'rh') === 0
            || strcasecmp($user->role->name, 'MANAGER') === 0;
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour un utilisateur
     */
    public function update(User $user, User $target): bool
    {
        if (!$user->role) return false;
        // RH ou Manager peuvent modifier n'importe quel utilisateur
        if (strcasecmp($user->role->name, 'rh') === 0 || strcasecmp($user->role->name, 'MANAGER') === 0) {
            return true;
        }

        // Un utilisateur peut modifier son propre profil
        return $user->id === $target->id;
    }

    /**
     * Détermine si l'utilisateur peut restaurer un utilisateur
     */
    public function restore(User $user, User $target): bool
    {
        // Seul un Manager peut restaurer
        return $user->role && strtoupper($user->role->name) === 'MANAGER';
    }

    /**
     * Détermine si l'utilisateur peut activer/désactiver un utilisateur
     */
    public function toggleActive(User $user, User $target): bool
    {
        // Seul un Manager peut activer/désactiver
        return $user->role && strtoupper($user->role->name) === 'MANAGER';
    }
}
