<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
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
            return true;
        }

        return false;
    }

    public function update(User $currentUser, User $targetUser)
{
    

    // RH peut modifier tous les profils
    if ($currentUser->role && strtoupper($currentUser->role->name) === 'RH') {
        return true;
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
