<?php

namespace App\Http\Controllers;

use App\Services\ProfileService;

use App\Http\Requests\ModifierStaffRequest;
use Illuminate\Support\Facades\Auth;



class ProfileController extends Controller
{
   protected $service;

    public function __construct(ProfileService $service)
    {
        $this->service = $service;
    }

   public function show()
{
    $user = Auth::user(); 

    try {
        // Vérifie l'autorisation avec la policy 'view'
        $this->authorize('view', $user);
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        // Debug complet pour comprendre le 403
        dd([
            'message' => $e->getMessage(),
            'user_id' => $user->id ?? null,
            'user_email' => $user->email ?? null,
            'user_role' => $user->role->nom ?? null,
            'trace' => $e->getTrace()
        ]);
    }

    $profile = $this->service->getProfile();

    return response()->json($profile, 200);
}


    public function update(ModifierStaffRequest $request)
    {
        $user = Auth::user();
       try {
        // Vérifie l'autorisation avec la policy 'update'
        $this->authorize('update', $user);
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        // Affiche toutes les infos pour comprendre le blocage
        dd([
            'message' => $e->getMessage(),
            'user_id' => $user->id ?? null,
            'user_email' => $user->email ?? null,
            'user_role' => $user->role->nom ?? null, // si tu as une relation role
            'request_data' => $request->all(),      // voir ce qui est envoyé
            'trace' => $e->getTrace()
        ]);
    }
        $updatedProfile = $this->service->updateProfile($request->validated());

        if (empty($updatedProfile)) {
            return response()->json([
                'message' => 'Aucun champ à mettre à jour'
            ], 400);
        }


        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'rh' => $updatedProfile
        ], 200);
    }    
}
