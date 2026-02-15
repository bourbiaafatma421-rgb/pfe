<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Http\Requests\ModifierStaffRequest;
use Illuminate\Support\Facades\Auth; 


class ProfileController extends Controller
{
    public function show()
    {
        $rh = Auth::user(); // récupère l'utilisateur connecté

        // Formater les données comme dans ton index
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
            'role' => $rh->role ? $rh->role->nom : null,
        ];

        return response()->json($rhFormatted, 200);
    }

    // Modifier son profil RH
    public function update(ModifierStaffRequest $request)
    {
        $rh = Auth::user();

        $fieldsToUpdate = array_intersect_key(
            $request->validated(),
            array_flip(['numero_telephone'])
        );

        if (empty($fieldsToUpdate)) {
            return response()->json([
                'message' => 'Aucun champ à mettre à jour'
            ], 400);
        }

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
            'role' => $rh->role ? $rh->role->nom : null,
        ];

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'rh' => $rhFormatted
        ], 200);
    }
    
}
