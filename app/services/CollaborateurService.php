<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CollaborateurService
{
    /**
     * Créer un collaborateur
     */
    public function createCollaborateur(array $data): array
    {
        DB::beginTransaction();
        try {
            // Générer un mot de passe temporaire
            $password = Str::random(8);

            // Normaliser le nom du rôle : minuscules + trim
            $roleName = strtolower(trim($data['role']));

            // Vérifier si le rôle existe déjà (insensible à la casse)
            $role = Role::whereRaw('LOWER(name) = ?', [$roleName])->first();

            // Si le rôle n'existe pas, on le crée
            if (!$role) {
                $role = Role::create(['name' => $roleName]);
            }
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($password),
                'role_id' => $role->id,
                'phone_number' => $data['phone_number'] ?? null,
                'date_of_hire' => $data['date_of_hire'] ?? null,
                'active' => true,
            ]);

            DB::commit();

            return [
                'user' => $user,
                'password' => $password,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Lister les collaborateurs avec filtres
     */
    public function getCollaborateurs(array $filters)
    {
        $query = User::with('role')
            ->whereHas('role', fn($q) => $q->where('name', 'new_collaborateur'));

        if (!empty($filters['first_name'])) {
            $query->where('first_name', 'ilike', '%' . $filters['first_name'] . '%');
        }

        if (!empty($filters['last_name'])) {
            $query->where('last_name', 'ilike', '%' . $filters['last_name'] . '%');
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        return $query->paginate(10);
    }

    /**
     * Mettre à jour un collaborateur
     */
    public function updateCollaborateur(User $collaborateur, array $data, User $user): User
    {
        // RH peut modifier rôle et numéro
        if ($user->isRh()) {
            if (!empty($data['role'])) {
                $role = Role::where('name', $data['role'])->firstOrFail();
                $collaborateur->role_id = $role->id;
            }
            $collaborateur->phone_number = $data['phone_number'] ?? $collaborateur->phone_number;
            $collaborateur->save();
        }
        // Collaborateur peut seulement modifier son numéro
        elseif ($user->isCollaborateur() && $user->id === $collaborateur->id) {
            $collaborateur->update([
                'phone_number' => $data['phone_number'] ?? $collaborateur->phone_number,
            ]);
        }

        return $collaborateur;
    }
}
