<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    public function setPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }

        if (!$user->active) {
            return response()->json([
                'message' => 'Compte désactivé'
            ], 403);
        }

        $user->password = Hash::make($request->new_password);
        $user->password_changed = true;
        $user->save();

        return response()->json([
            'message' => 'Mot de passe défini avec succès. Vous pouvez maintenant vous connecter.'
        ], 200);
    }
}
