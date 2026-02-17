<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use App\Exceptions\Auth\UserNotFoundException;
use App\Exceptions\Auth\AccountInactiveException;
use App\Exceptions\Auth\ForcePasswordChangeException;
use Illuminate\Support\Facades\Hash;



class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function login(LoginRequest $request)
    {
<<<<<<< HEAD
        try {
            $data = $this->authService->login($request->email, $request->password);

            $response = [
                'message' => $data['force_password_change'] ? 'Changement de mot de passe obligatoire' : 'Connexion réussie',
                'token' => $data['token'],
=======
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
>>>>>>> origin/main
                'user' => [
                    'id' => $data['user']->id,
                    'email' => $data['user']->email,
                    'role' => $data['user']->role->name ?? null,
                    'active' => $data['user']->active
                ]
            ];

            if ($data['force_password_change']) {
                $response['force_password_change'] = true;
            }

            return response()->json($response, 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

<<<<<<< HEAD
    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Déconnexion réussie']);
=======
    // Déconnexion
    public function logout(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user(); // Typage explicite pour Intelephense
        $user->tokens()->delete();

        return response()->json([
            'message' => 'La déconnexion a été effectuée avec succès'
        ]);
>>>>>>> origin/main
    }

}
