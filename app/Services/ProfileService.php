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
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'active' => $user->active,
            'date_of_hire' => $user->date_of_hire
                ? Carbon::parse($user->date_of_hire)->format('d-m-Y')
                : null,
            'phone_number' => $user->phone_number,
            'role' => $user->role ? $user->role->name : null,
        ];
    }
    //mettre a jour le profil de l'utilisateur connecté
    public function updateProfile(array $data): array
    {
        $user = Auth::user();

        $fieldsToUpdate = array_intersect_key(
            $data,
            array_flip(['phone_number'])
        );

        if (!empty($fieldsToUpdate)) {
            $user->update($fieldsToUpdate);
        }

        return $this->getProfile();
    }
}
