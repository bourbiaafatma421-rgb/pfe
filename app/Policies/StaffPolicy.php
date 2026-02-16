<?php

namespace App\Policies;

use App\Models\User;

class StaffPolicy
{
    // Détermine si l'utilisateur peut voir tous les staffs
    public function viewAny(User $user): bool
    {
        return $user->hasRole('manager');
    }

    // Détermine si l'utilisateur peut voir un staff spécifique
    public function view(User $user): bool
    {
        return $user->hasRole('manager');
    }

    // Détermine si l'utilisateur peut créer un staff
    public function create(User $user): bool
    {
        return $user->hasRole('manager');
    }

    // Détermine si l'utilisateur peut mettre à jour un staff
    public function update(User $user): bool
    {
        return $user->hasRole('manager');
    }

    // Pas de restauration pour l'instant
    public function restore(User $user): bool
    {
        return false;
    }
}
