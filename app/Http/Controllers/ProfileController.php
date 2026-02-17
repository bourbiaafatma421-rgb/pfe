<?php

namespace App\Http\Controllers;

use App\Services\ProfileService;

use App\Http\Requests\ModifierStaffRequest;
use Illuminate\Support\Facades\Auth;



class ProfileController extends Controller
{
<<<<<<< HEAD
   protected $service;

    public function __construct(ProfileService $service)
    {
        $this->service = $service;
    }
=======
    public function show()
{
        $rh = Auth::user();
        return response()->json([
            'id' => $rh->id,
            'email' => $rh->email,
            'nom' => $rh->nom,
            'prenom' => $rh->prenom,
            'active' => $rh->active,
            'date_recrutement' => $rh->date_recrutement
                ? Carbon::parse($rh->date_recrutement)->format('d-m-Y')
                : null,
            'numero_telephone' => $rh->numero_telephone,
            'role' => $rh->role?->name,
        ], 200);
}
>>>>>>> origin/main

    public function show()
    {
        $user = Auth::user(); 


        $this->authorize('view', $user);


        $profile = $this->service->getProfile();

        return response()->json($profile, 200);
    }


    public function update(ModifierStaffRequest $request)
    {
<<<<<<< HEAD
        $user = Auth::user();

        
        $this->authorize('update', $user);
=======
        $rh = User::findOrFail(Auth::id());
        $fieldsToUpdate = array_intersect_key(
            $request->validated(),
            array_flip(['numero_telephone'])
        );
>>>>>>> origin/main

        $updatedProfile = $this->service->updateProfile($request->validated());

        if (empty($updatedProfile)) {
            return response()->json([
                'message' => 'Aucun champ à mettre à jour'
            ], 400);
        }

<<<<<<< HEAD
=======
        $rh->update($fieldsToUpdate);

        $rhFormatted = [
            'id' => $rh->id,
            'email' => $rh->email,
            'nom' => $rh->nom,
            'prenom' => $rh->prenom,
            'active' => $rh->active,
            'date_recrutement' => $rh->date_recrutement 
                ? Carbon::parse($rh->date_recrutement)->format('d-m-Y')
                : null,
            'numero_telephone' => $rh->numero_telephone,
            'role' => $rh->role ? $rh->role->name : null,
        ];
>>>>>>> origin/main

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'rh' => $updatedProfile
        ], 200);
    }    
}
