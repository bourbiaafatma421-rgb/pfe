<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        // Tentative d'authentification
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Identifiants invalides'
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user(); // Typage explicite pour Intelephense

        if (!$user->active) {
            return response()->json([
                'message' => 'Compte désactivé'
            ], 403);
        }

        // Supprimer les anciens tokens et en créer un nouveau
        $user->tokens()->delete(); // fonctionne si HasApiTokens est présent
        $token = $user->createToken('api-token-' . now())->plainTextToken;

        // Forcer changement mot de passe si pas encore changé
        if (!$user->password_changed) {
            return response()->json([
                'message' => 'Changement de mot de passe obligatoire',
                'force_password_change' => true,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email
                ]
            ], 200);
        }

        return response()->json([
            'message' => 'Connexion réussie',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role->name ?? null,
                'active' => $user->active
            ]
        ], 200);
    }

    // Déconnexion
    public function logout(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user(); // Typage explicite pour Intelephense
        $user->tokens()->delete();

        return response()->json([
            'message' => 'La déconnexion a été effectuée avec succès'
        ]);
    }
}
