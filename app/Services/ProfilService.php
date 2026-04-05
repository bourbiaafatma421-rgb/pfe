<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfilService
{
    //  Récupérer le profil connecté 

    public function getMonProfil(): User
    {
        /** @var User $user */
        $user = Auth::user();
        return $user->load('role');
    }

    //  Modifier le téléphone 

    public function updateTelephone(User $user, string $phone): User
    {
        $user->phone_number = $phone;
        $user->save();
        return $user->load('role');
    }

    //  Upload avatar 

    public function updateAvatar(User $user, $file): User
    {
        // Supprimer l'ancien avatar si existe
        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $path = $file->store('avatars', 'public');
        $user->avatar_path = $path;
        $user->save();

        return $user->load('role');
    }

    //  Supprimer avatar 

    public function deleteAvatar(User $user): User
    {
        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->avatar_path = null;
        $user->save();

        return $user->load('role');
    }
}