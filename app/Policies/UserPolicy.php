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
        // Si l'utilisateur connecté est un Manager et que la cible est un RH
        if ($user->role && strtoupper($user->role->name) === 'MANAGER') {
            if ($target->role && strtoupper($target->role->name) === 'RH') {
                return true; //  Manager peut voir les détails d'un RH
            }
        }

        // Sinon, un utilisateur peut voir seulement son propre profil
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
