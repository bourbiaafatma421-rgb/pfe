<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validation
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        // Auth attempt
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Identifiants invalides'
            ], 401);
        }

        $user = Auth::user();
        if (!$user->active) {
            Auth::logout();
            return response()->json([
                'message' => 'Compte désactivé. Contactez l’administrateur.'
            ], 403);
        }
        // Ici, tu peux renvoyer les infos et le rôle
        return response()->json([
            'message' => 'Connexion réussie',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'active' => $user->active
            ]
        ], 200);
    }
}
