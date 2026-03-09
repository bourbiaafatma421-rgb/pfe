<?php

namespace App\Services;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

use App\Exceptions\Staff\StaffNotFoundException;
use App\Exceptions\Staff\StaffUpdateForbiddenException;
use App\Exceptions\Staff\ManagerAlreadyExistsException;
use App\Exceptions\Staff\InvalidRHException;

class StaffService
{

    public function listRH(array $filters = [])
    {
        $query = User::with('role')
        ->whereHas('role', fn($q) => $q->whereRaw('LOWER(name) = ?', ['rh']));

        if (!empty($filters['first_name'])) {
            $query->where('first_name', 'ilike', "%{$filters['first_name']}%");
        }

        if (!empty($filters['last_name'])) {
            $query->where('last_name', 'ilike', "%{$filters['last_name']}%");
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
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'active' => $user->active,
                'date_of_hire' => $user->date_of_hire ? Carbon::parse($user->date_of_hire)->format('d-m-Y') : null,
                'phone_number' => $user->phone_number,
                'role' => $user->role?->name,
            ];
        });
    }
    public function getById(int $id): User{
        return User::with('role')->findOrFail($id);
}

    
    public function createRH(array $data)
    {
        $password = Str::random(8);
        $role = Role::firstOrCreate(['name' => 'rh']);
        $user = User::create([
            'email' => $data['email'],
            'password' => Hash::make($password),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'role_id' => $role->id,
            'active' => 1,
            'date_of_hire' => $data['date_of_hire'],
            'phone_number' => $data['phone_number'],
        ]);

        return ['user' => $user, 'password_temporaire' => $password];
    }

    
    public function updateRH(User $user, array $data, User $currentUser)
    {
        $allowed = ['phone_number', 'date_of_hire'];

        if (strtoupper($currentUser->role->name) === 'RH' && $currentUser->id !== $user->id) {
            throw new StaffUpdateForbiddenException("Un RH ne peut modifier que son propre profil");
        }

        
        if (strtoupper($currentUser->role->name) === 'MANAGER') {
            $rhRole = Role::whereRaw('LOWER(name) = ?', ['rh'])->first();
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

    
    public function createManager(array $data)
    {
        $managerRole = Role::firstOrCreate(['name' => 'MANAGER']);

        if (User::where('role_id', $managerRole->id)->exists()) {
            throw new ManagerAlreadyExistsException();        
        }

        $password = Str::random(10);

        $user = User::create([
            'email' => $data['email'],
            'password' => Hash::make($password),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'role_id' => $managerRole->id,
            'active' => 1,
            'date_of_hire' => $data['date_of_hire'],
            'phone_number' => $data['phone_number'],
        ]);

        return ['user' => $user, 'password_temporaire' => $password];
    }

    
    public function toggleActiveRH(User $user)
    {
        $rhId = Role::whereRaw('LOWER(name) = ?', ['rh'])->value('id');
        if ($user->role_id !== $rhId) {
            throw new InvalidRHException();        
        }

        $user->active = !$user->active;
        $user->save();

        return $user;
    }
}
