<?php

namespace App\Http\Controllers;

use App\Http\Requests\AjoutStaffRequest;
use App\Http\Requests\ModifierStaffRequest;
use App\Http\Requests\DeleteStaffRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\AjoutManagerRequest;
use Carbon\Carbon;

class StaffController extends Controller
{
    
    // Lister tous les staffs (manager + RH)
     
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        // On commence par tous les RH
        $query = User::with('role')
        ->whereHas('role', function($lerole) {
            $lerole->where('nom', 'rh');
        });

        if ($request->filled('nom')) {
            $query->where('nom', 'ilike', "%{$request->nom}%");
        }

        if ($request->filled('prenom')) {
            $query->where('prenom', 'ilike', "%{$request->prenom}%");
        }

        if ($request->filled('active')) {
            if ($request->active === 'true') {
                $query->where('active', true);
            } elseif ($request->active === 'false') {
                $query->where('active', false);
            }
        }
        
        $users = $query->get();

        if ($users->isEmpty()) {
            return response()->json([
                'message' => 'Aucun RH ne correspond aux critères de recherche.'
            ], 404);
        }
        $usersFormatted = $users->map(function($user) {
            return [
                'id' => $user->id,
                'email' => $user->email,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'active' => $user->active,
                'date_recrutement' => $user->date_recrutement 
                ? \Carbon\Carbon::parse($user->date_recrutement)->format('d-m-Y')  . ''
                : null,            
                'numero_telephone' => $user->numero_telephone,
                'role' => $user->role ? $user->role->nom : null,
            ];
        });
        return response()->json($usersFormatted, 200);
    }

    public function toggleActive($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'RH non trouvé'], 404);
        }

        $rhId = Role::where('nom', 'rh')->value('id');
        if ($user->role_id != $rhId) {
            return response()->json(['message' => 'L’utilisateur doit être un RH'], 403);
        }

        $user->active = !$user->active;
        $user->save();

        return response()->json([
            'message' => $user->active ? 'Compte activé avec succès' : 'Compte désactivé avec succès',
            'active' => $user->active
        ]);
    }

    //Ajouter un RH
     
    public function store(AjoutStaffRequest $request)
    {
        try {
            $this->authorize('create', User::class);
            $data = $request->validated();

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

            return response()->json([
                'message' => "RH ajouté avec succès",
                'user' => [
                    'email' => $user->email,
                    'password_temporaire' => $password
                ]
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du RH',
                'erreur' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
     
     //Modifier un staff 
     
    public function update(ModifierStaffRequest $request, $id)
    {
        try {
            // Vérifie si le staff existe
            $user = User::with('role')->find($id);
            if (!$user) {
                return response()->json([
                    'message' => 'Staff non trouvé'
                ], 404);
            }

            $currentUser = auth()->user();
            if (!$currentUser) {
                return response()->json([
                    'message' => 'Utilisateur non authentifié'
                ], 401);
            }

            if (!$currentUser->role) {
                return response()->json([
                    'message' => 'Rôle de l’utilisateur manquant'
                ], 403);
            }

            $allowed = ['numero_telephone', 'date_recrutement'];

            if (strtoupper($currentUser->role->nom) === 'RH') {
                if ($currentUser->id !== $user->id) {
                    return response()->json([
                        'message' => "Accès refusé : un RH ne peut modifier que son propre profil"
                    ], 403);
                }
            } elseif (strtoupper($currentUser->role->nom) === 'MANAGER') {
                $rhRole = Role::whereRaw('LOWER(nom) = ?', ['rh'])->first();
                if (!$rhRole) {
                    return response()->json([
                        'message' => 'Rôle RH introuvable'
                    ], 404);
                }

                if (!$user->role || $user->role_id !== $rhRole->id) {
                    return response()->json([
                        'message' => 'Seuls les RH peuvent être modifiés par un MANAGER'
                    ], 403);
                }
            } else {
                return response()->json([
                    'message' => 'Rôle non autorisé à modifier un staff'
                ], 403);
            }

            $data = array_intersect_key($request->validated(), array_flip($allowed));

            if (empty($data)) {
                return response()->json([
                    'message' => 'Aucun champ valide à mettre à jour'
                ], 400);
            }

            $user->update($data);

            return response()->json([
                'message' => 'Staff modifié avec succès',
                'staff' => $user
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du staff',
                'erreur' => $e->getMessage(),
                //'trace' => $e->getTraceAsString() 
            ], 500);
        }
    }

    public function storeManager(AjoutManagerRequest $request)
    {
        $managerRole = Role::firstOrCreate(
            ['nom' => 'MANAGER'] 
        );

        if (User::where('role_id', $managerRole->id)->exists()) {
            return response()->json([
                'message' => 'Un manager existe déjà'
            ], 409);
        }

        $data = $request->validated();
        $password = Str::random(10);

        $user = User::create([
            'email' => $data['email'],
            'password' => Hash::make($password),
            'nom'=>$data['nom'],
            'prenom'=>$data['prenom'],
            'role_id' => $managerRole->id,
            'active' => 1,
            'date_recrutement'=>$data['date_recrutement'],
            'numero_telephone'=>$data['numero_telephone'],

        ]);


        return response()->json([
            'message' => 'Manager créé avec succès',
            'password_temporaire' => $password
        ], 201);
    }
    
}


