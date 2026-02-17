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


        $this->authorize('view', $user);


        $profile = $this->service->getProfile();

        return response()->json($profile, 200);
    }


    public function update(ModifierStaffRequest $request)
    {
        $user = Auth::user();

        
        $this->authorize('update', $user);

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
