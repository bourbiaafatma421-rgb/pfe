<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\Auth\UserNotFoundException;
use App\Exceptions\Auth\AccountInactiveException;
use Illuminate\Support\Facades\Hash;


class AuthService
{
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw new \Exception('Identifiants invalides', 401);
        }

        if (!$user->active) {
            throw new \Exception('Compte désactivé', 403);
        }

        // Supprimer anciens tokens
        $user->tokens()->delete();
        $token = $user->createToken('api-token-' . now()->timestamp)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'force_password_change' => !$user->password_changed
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete(); // Supprime le token courant
    }
}
