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

        try {
            $data = $this->authService->login($request->email, $request->password);

            $response = [
                'message' => $data['force_password_change'] ? 'Changement de mot de passe obligatoire' : 'Connexion réussie',
                'token' => $data['token'],
                
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


    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Déconnexion réussie']);
    }

}
