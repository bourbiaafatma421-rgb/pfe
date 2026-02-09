<?php

namespace App\Services;

use App\Models\User;
use App\Models\Collaborateur;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;


class CollaborateurService{
    public function createCollaborateur($data)
    {
      DB::beginTransaction();
        try{
        $password = Str::random(8);

        $user = User::create([
            'email' => $data->email,
            'password' => Hash::make($password),
            'role' => 'collaborateur',
        ]);

        $collab = Collaborateur::create([
            'user_id' => $user->id,
            'nom' => $data->nom,
            'prenom' => $data->prenom,
            'numero_telephone' => $data->numero_telephone,
            'poste' => $data->poste,
            'etat' => $data->etat ?? 'encours',
        ]);
        DB::commit();
        return response()->json([
            'message' => 'Collaborateur créé avec succès',
            'email' => $user->email,
            'password_temporaire' => $password
            ]
        , 201);
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
}
}