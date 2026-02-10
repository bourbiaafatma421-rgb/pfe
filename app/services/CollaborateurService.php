<?php

namespace App\Services;

use App\Models\User;
use App\Models\Collaborateur;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CollaborateurService{
    public function createCollaborateur($data)
    {
        try{
             DB::beginTransaction();
             $password = Str::random(8);
             $role = Role::where('name', $data->role)->firstOrFail();
             $user = User::create([
                    'nom' => $data->nom,
                    'prenom' => $data->prenom,
                    'email' => $data->email,
                    'password' => Hash::make($password),
                    'role_id' => $role->id, 
                    'numero_telephone' => $data->numero_telephone ?? null,
                    'date_recrutement' => $data->date_recrutement ?? null,
                    'active' => true,
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
public function getCollaborateurs(array $filters)
{
    $query = User::with('role')
        ->whereHas('role', function ($q) {
            $q->where('name', 'new_collaborateur'); 
        });

    if (!empty($filters['nom'])) {
        $query->where('nom', 'ilike', '%' . $filters['nom'] . '%');
    }

    if (!empty($filters['prenom'])) {
        $query->where('prenom', 'ilike', '%' . $filters['prenom'] . '%');
    }

    if (isset($filters['active'])) {
        $query->where('active', $filters['active']);
    }

    return $query->paginate(10);
}
public function updatecollaborateur($collaborateur, Array $data, $user){
     if ($user->role?->name === 'rh') {
        $collaborateur->update([
            'poste' => $data['poste'] ?? $collaborateur->poste,
            'numero_telephone' => $data['numero_telephone'] ?? $collaborateur->numero_telephone,
            'etat' => $data['etat'] ?? $collaborateur->etat,
        ]);
    }elseif ($user->role?->name === 'collaborateur' && $user->id === $collaborateur->user_id) {
        $collaborateur->update([
            'numero_telephone' => $data['numero_telephone'] ?? $collaborateur->numero_telephone,
        ]);
    }
    return $collaborateur;

}

}