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
            $role = Role::whereRaw('LOWER(nom) = ?', [$roleName])->first();

            // Si le rôle n'existe pas, on le crée
            if (!$role) {
                $role = Role::create(['nom' => $roleName]);
            }
            $user = User::create([
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'],
                'password' => Hash::make($password),
                'role_id' => $role->id,
                'numero_telephone' => $data['numero_telephone'] ?? null,
                'date_recrutement' => $data['date_recrutement'] ?? null,
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
            $collaborateur->numero_telephone = $data['numero_telephone'] ?? $collaborateur->numero_telephone;
            $collaborateur->save();
        }
        // Collaborateur peut seulement modifier son numéro
        elseif ($user->isCollaborateur() && $user->id === $collaborateur->id) {
            $collaborateur->update([
                'numero_telephone' => $data['numero_telephone'] ?? $collaborateur->numero_telephone,
            ]);
        }

        return $collaborateur;
    }
}