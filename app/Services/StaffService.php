<?php

namespace App\Services;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
// Exceptions personnalisées
use App\Exceptions\Staff\StaffNotFoundException;
use App\Exceptions\Staff\StaffUpdateForbiddenException;
use App\Exceptions\Staff\ManagerAlreadyExistsException;
use App\Exceptions\Staff\InvalidRHException;

class StaffService
{
    //lister les RH
    public function listRH(array $filters = [])
    {
        $query = User::with('role')
        ->whereHas('role', fn($q) => $q->whereRaw('LOWER(nom) = ?', ['rh']));

        if (!empty($filters['nom'])) {
            $query->where('nom', 'ilike', "%{$filters['nom']}%");
        }

        if (!empty($filters['prenom'])) {
            $query->where('prenom', 'ilike', "%{$filters['prenom']}%");
        }

        if (isset($filters['active'])) {
            if ($filters['active'] === true || $filters['active'] === 'true') {
                $query->where('active', true);
            } elseif ($filters['active'] === false || $filters['active'] === 'false') {
                $query->where('active', false);
            }
        }

        $users = $query->get();

        return $users->map(function($user) {
            return [
                'id' => $user->id,
                'email' => $user->email,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'active' => $user->active,
                'date_recrutement' => $user->date_recrutement ? Carbon::parse($user->date_recrutement)->format('d-m-Y') : null,
                'numero_telephone' => $user->numero_telephone,
                'role' => $user->role?->nom,
            ];
        });
    }

    //ajouter un rh 
    public function createRH(array $data)
    {
        $password = Str::random(8);
        $role = Role::firstOrCreate(['nom' => 'rh']);

        $user = User::create([
            'email' => $data['email'],
            'password' => Hash::make($password),
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'role_id' => $role->id,
            'active' => 1,
            'date_recrutement' => $data['date_recrutement'],
            'numero_telephone' => $data['numero_telephone'],
        ]);

        return ['user' => $user, 'password_temporaire' => $password];
    }

    //modifier un rh
    public function updateRH(User $user, array $data, User $currentUser)
    {
        $allowed = ['numero_telephone', 'date_recrutement'];

        if (strtoupper($currentUser->role->nom) === 'RH' && $currentUser->id !== $user->id) {
            throw new StaffUpdateForbiddenException("Un RH ne peut modifier que son propre profil");
        }

        // MANAGER peut modifier uniquement des RH
        if (strtoupper($currentUser->role->nom) === 'MANAGER') {
            $rhRole = Role::whereRaw('LOWER(nom) = ?', ['rh'])->first();
            if (!$rhRole || $user->role_id !== $rhRole->id) {
                throw new StaffUpdateForbiddenException('Seuls les RH peuvent être modifiés par un MANAGER');
            }
        }

        $updateData = array_intersect_key($data, array_flip($allowed));
        if (empty($updateData)) {
            throw new StaffUpdateForbiddenException('Aucun champ valide à mettre à jour');
        }

        $user->update($updateData);
        return $user;
    }

    //ajouter un manager et un seul manager 
    public function createManager(array $data)
    {
        $managerRole = Role::firstOrCreate(['nom' => 'MANAGER']);

        if (User::where('role_id', $managerRole->id)->exists()) {
            throw new ManagerAlreadyExistsException();        
        }

        $password = Str::random(10);

        $user = User::create([
            'email' => $data['email'],
            'password' => Hash::make($password),
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'role_id' => $managerRole->id,
            'active' => 1,
            'date_recrutement' => $data['date_recrutement'],
            'numero_telephone' => $data['numero_telephone'],
        ]);

        return ['user' => $user, 'password_temporaire' => $password];
    }

    //activer ou desactiver un compte de rh
    public function toggleActiveRH(User $user)
    {
        $rhId = Role::whereRaw('LOWER(nom) = ?', ['rh'])->value('id');
        if ($user->role_id !== $rhId) {
            throw new InvalidRHException();        
        }

        $user->active = !$user->active;
        $user->save();

        return $user;
    }
}
