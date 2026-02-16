<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role && in_array(strtoupper($user->role->nom), ['MANAGER']);
    }

    public function view(User $user, User $model)
    {
        logger()->info('DEBUG UserPolicy:view', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_role' => $user->role->nom ?? null,
            'model_id' => $model->id,
        ]);

        if ($user->role && in_array(strtoupper($user->role->nom), ['rh', 'MANAGER'])) {
            return true;
        }

        if ($user->id === $model->id) {
            return true;
        }

        return false;
    }

    public function update(User $currentUser, User $targetUser)
{
    // Log pour debug
    logger()->info('Policy update check', [
        'current_user_id' => $currentUser->id,
        'current_user_role' => $currentUser->role->nom ?? null,
        'target_user_id' => $targetUser->id,
        'target_user_role' => $targetUser->role->nom ?? null,
    ]);

    // RH peut modifier tous les profils
    if ($currentUser->role && strtoupper($currentUser->role->nom) === 'RH') {
        return true;
    }

    // Manager peut aussi modifier les profils
    if ($currentUser->role && strtoupper($currentUser->role->nom) === 'MANAGER') {
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
        return $user->role && in_array(strtoupper($user->role->nom), ['rh', 'MANAGER']);
    }

    public function delete(User $user, User $model)
    {
        if ($user->id === $model->id) return false;

        return $user->role && in_array(strtoupper($user->role->nom), ['rh', 'MANAGER']);
    }

    public function restore(User $user, User $model)
    {
        return $user->role && in_array(strtoupper($user->role->nom), ['rh', 'MANAGER']);
    }

    public function forceDelete(User $user, User $model)
    {
        // Force delete uniquement pour rh
        return $user->role && strtoupper($user->role->nom) === 'rh';
    }
    public function toggleActive(User $currentUser, User $targetUser)
{
    // RH ou Manager peuvent activer/désactiver
    if (in_array(strtoupper($currentUser->role->nom ?? ''), ['RH', 'MANAGER'])) {
        return true;
    }

    return false; // sinon interdit
}
}
