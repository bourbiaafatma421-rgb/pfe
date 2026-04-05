<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\CollaborateurBienvenueMail;

class CollaborateurService
{
    //  Créer un collaborateur ──

    public function createCollaborateur(array $data): array
    {
        DB::beginTransaction();
        try {
            $password = Str::random(8);

            $roleName = strtolower(trim($data['role']));
            $role = Role::whereRaw('LOWER(name) = ?', [$roleName])->first();
            if (!$role) {
                $role = Role::create(['name' => $roleName]);
            }

            // Générer token signature unique
            $signatureToken = Str::random(64);

            $user = User::create([
                'last_name'       => $data['last_name'],
                'first_name'      => $data['first_name'],
                'email'           => $data['email'],
                'password'        => Hash::make($password),
                'role_id'         => $role->id,
                'phone_number'    => $data['phone_number'] ?? null,
                'date_of_hire'    => $data['date_of_hire'] ?? null,
                'active'          => true,
                'signature_token' => $signatureToken,
            ]);

            DB::commit();

            //  Envoi email de bienvenue avec QR Code 
            try {
                $signatureUrl = config('app.frontend_url') . '/sign/' . $signatureToken;

                Mail::to($user->email)->send(new CollaborateurBienvenueMail(
                    prenom:       $user->first_name,
                    nom:          $user->last_name,
                    email:        $user->email,
                    motDePasse:   $password,
                    role:         $role->name,
                    signatureUrl: $signatureUrl,
                ));
            } catch (\Exception $mailException) {
                Log::warning('Email bienvenue non envoyé: ' . $mailException->getMessage());
            }

            return [
                'user'     => $user,
                'password' => $password,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    //  Lister les collaborateurs avec filtres ─

    public function getCollaborateurs(array $filters)
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        $query = User::with('role');

        // RH voit tous sauf les managers
        if ($authUser->isRh()) {
            $query->whereHas('role', fn($q) => $q->where('name', '!=', 'manager'));
        }

        // Manager voit tous sauf lui-même
        if ($authUser->isManager()) {
            $query->where('id', '!=', $authUser->id);
        }

        // Filtre par rôle
        if (!empty($filters['role'])) {
            $query->whereHas('role', fn($q) => $q->where('name', $filters['role']));
        }

        // Filtre par statut
        if (isset($filters['active'])) {
            $query->where('active', filter_var($filters['active'], FILTER_VALIDATE_BOOLEAN));
        }

        // Filtre par nom
        if (!empty($filters['last_name'])) {
            $query->where('last_name', 'ilike', '%' . $filters['last_name'] . '%');
        }

        // Filtre par prénom
        if (!empty($filters['first_name'])) {
            $query->where('first_name', 'ilike', '%' . $filters['first_name'] . '%');
        }

        $query->orderBy('id', 'desc');
        return $query->paginate(10);
    }

    //  Récupérer un collaborateur par ID ──

    public function getCollaborateurById(int $id): User
    {
        return User::with('role')->findOrFail($id);
    }

    //  Mettre à jour un collaborateur ──

    public function updateCollaborateur(User $collaborateur, array $data, User $user): User
{
    if ($user->isRh()) {

        //  Update role
        if (!empty($data['role'])) {
            $role = Role::whereRaw('LOWER(name) = ?', [strtolower($data['role'])])->first();

            if ($role) {
                $collaborateur->role_id = $role->id;
            }
        }

        //  Update phone
        if (isset($data['phone_number'])) {
            $collaborateur->phone_number = $data['phone_number'];
        }

        $collaborateur->save();
        $collaborateur->load('role');

    } elseif ($user->isCollaborateur() && $user->id === $collaborateur->id) {

        $collaborateur->update([
            'phone_number' => $data['phone_number'] ?? $collaborateur->phone_number,
        ]);

        $collaborateur->load('role');
    }

    return $collaborateur;
}
    public function updateAvatar(User $user, string $path): User
{
    $user->avatar_path = $path;
    $user->save();
    return $user;
}
    //  Profil du collaborateur connecté 

    public function getMonProfil(): User
    {
        /** @var User $user */
        $user = Auth::user();
        return $user->load('role');
    }
}