<?php

namespace App\Services;

use App\Http\Requests\ModifierCollaborateurRequest;
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
public function getCollaborateurs($filters)
{
    $query = Collaborateur::with('user');

    if (!empty($filters['nom'])) {
        $query->where('nom', $filters['nom']);
    }

    if (!empty($filters['prenom'])) {
        $query->where('prenom', $filters['prenom']);
    }

    if (!empty($filters['etat'])) {
        $query->where('etat', $filters['etat']);
    }

    return $query->paginate(10);
}
public function updatecollaborateur($collaborateur, Array $data, $user){
     if ($user->role === 'rh') {
        $collaborateur->update([
            'poste' => $data['poste'] ?? $collaborateur->poste,
            'numero_telephone' => $data['numero_telephone'] ?? $collaborateur->numero_telephone,
            'etat' => $data['etat'] ?? $collaborateur->etat,
        ]);
    }elseif ($user->role === 'collaborateur' && $user->id === $collaborateur->user_id) {
        $collaborateur->update([
            'numero_telephone' => $data['numero_telephone'] ?? $collaborateur->numero_telephone,
        ]);
    }
    return $collaborateur;

}

}