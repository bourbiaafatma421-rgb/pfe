<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProfileService
{
    //afficher le profile de l'utilisateur connecté
    public function getProfile(): array
    {
        $user = Auth::user();

        return [
            'id' => $user->id,
            'email' => $user->email,
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'active' => $user->active,
            'date_recrutement' => $user->date_recrutement
                ? Carbon::parse($user->date_recrutement)->format('d-m-Y')
                : null,
            'numero_telephone' => $user->numero_telephone,
            'role' => $user->role ? $user->role->nom : null,
        ];
    }
    //mettre a jour le profil de l'utilisateur connecté
    public function updateProfile(array $data): array
    {
        $user = Auth::user();

        $fieldsToUpdate = array_intersect_key(
            $data,
            array_flip(['numero_telephone'])
        );

        if (!empty($fieldsToUpdate)) {
            $user->update($fieldsToUpdate);
        }

        return $this->getProfile();
    }
}
