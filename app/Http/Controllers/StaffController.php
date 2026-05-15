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
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::with('role')
            ->whereHas('role', function($lerole) {
                $lerole->where('name', 'rh');
            });

        if ($request->filled('last_name')) {
            $query->where('last_name', 'ilike', "%{$request->last_name}%");
        }

        if ($request->filled('first_name')) {
            $query->where('first_name', 'ilike', "%{$request->first_name}%");
        }

        if ($request->filled('active')) {
            if ($request->active === 'true') {
                $query->where('active', true);
            } elseif ($request->active === 'false') {
                $query->where('active', false);
            }
        }

        $users = $query->get();

        // ✅ Retourne tableau vide au lieu de 404
        if ($users->isEmpty()) {
            return response()->json([], 200);
        }

        $usersFormatted = $users->map(function($user) {
            return [
                'id'           => $user->id,
                'email'        => $user->email,
                'last_name'    => $user->last_name,   
                'first_name'   => $user->first_name,  
                'active'       => $user->active,
                'date_of_hire' => $user->date_of_hire
                    ? \Carbon\Carbon::parse($user->date_of_hire)->format('d/m/Y')
                    : null,
                'phone_number' => $user->phone_number, 
                'role'         => $user->role ? $user->role->name : null,
            ];
        });

        return response()->json($usersFormatted, 200);
    }

    public function toggleActive($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $this->authorize('update', $user);

        $rhId          = Role::where('name', 'rh')->value('id');
        $managerId     = Role::where('name', 'manager')->value('id');
        $connectedUser = Auth::user();

        if ($connectedUser->role_id === $managerId) {
            // Manager → peut tout faire
        } elseif ($connectedUser->role_id === $rhId) {
            if ($user->role_id === $managerId) {
                return response()->json([
                    'message' => 'Un RH ne peut pas désactiver un manager'
                ], 403);
            }
        } else {
            return response()->json(['message' => 'Action non autorisée'], 403);
        }

        $user->active = !$user->active;
        $user->save();

        return response()->json([
            'message' => $user->active ? 'Compte activé avec succès' : 'Compte désactivé avec succès',
            'active'  => $user->active
        ]);
    }

    public function store(AjoutStaffRequest $request)
    {
        try {
            $this->authorize('create', User::class);
            $data = $request->validated();

            $password = Str::random(8);
            $role = Role::firstOrCreate(['name' => 'rh']);

            $user = User::create([
                'email'        => $data['email'],
                'password'     => Hash::make($password),
                'last_name'    => $data['last_name'],  
                'first_name'   => $data['first_name'], 
                'role_id'      => $role->id,
                'active'       => 1,
                'date_of_hire' => $data['date_of_hire'],   
                'phone_number' => $data['phone_number'],   
            ]);

            return response()->json([
                'message' => "RH ajouté avec succès",
                'user' => [
                    'email'               => $user->email,
                    'password_temporaire' => $password
                ]
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du RH',
                'erreur'  => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ], 500);
        }
    }

    public function update(ModifierStaffRequest $request, $id)
    {
        try {
            $user = User::with('role')->find($id);
            if (!$user) {
                return response()->json(['message' => 'Staff non trouvé'], 404);
            }

            $currentUser = Auth::user();
            if (!$currentUser) {
                return response()->json(['message' => 'Utilisateur non authentifié'], 401);
            }

            if (!$currentUser->role) {
                return response()->json(['message' => 'Rôle de l\'utilisateur manquant'], 403);
            }

            $allowed = ['phone_number', 'date_of_hire']; 

            if (strtoupper($currentUser->role->name) === 'RH') {
                if ($currentUser->id !== $user->id) {
                    return response()->json([
                        'message' => "Accès refusé : un RH ne peut modifier que son propre profil"
                    ], 403);
                }
            } elseif (strtoupper($currentUser->role->name) === 'MANAGER') {
                $rhRole = Role::whereRaw('LOWER(name) = ?', ['rh'])->first();
                if (!$rhRole) {
                    return response()->json(['message' => 'Rôle RH introuvable'], 404);
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
                return response()->json(['message' => 'Aucun champ valide à mettre à jour'], 400);
            }

            $user->update($data);

            return response()->json([
                'message' => 'Staff modifié avec succès',
                'staff'   => $user
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du staff',
                'erreur'  => $e->getMessage(),
            ], 500);
        }
    }

    public function storeManager(AjoutManagerRequest $request)
    {
        $roleName    = 'manager';
        $managerRole = Role::whereRaw('LOWER(name) = ?', [strtolower($roleName)])->first();

        if (!$managerRole) {
            $managerRole = Role::create(['name' => $roleName]);
        }

        if (User::where('role_id', $managerRole->id)->exists()) {
            return response()->json(['message' => 'Un manager existe déjà'], 409);
        }

        $data     = $request->validated();
        $password = Str::random(10);

        $user = User::create([
            'email'        => $data['email'],
            'password'     => Hash::make($password),
            'last_name'    => $data['last_name'],  
            'first_name'   => $data['first_name'], 
            'role_id'      => $managerRole->id,
            'active'       => 1,
            'date_of_hire' => $data['date_of_hire'],   
            'phone_number' => $data['phone_number'],   
        ]);

        return response()->json([
            'message'              => 'Manager créé avec succès',
            'password_temporaire'  => $password
        ], 201);
    }
    public function show(int $id)
{
    $user = User::with('role')->find($id);

    if (!$user) {
        return response()->json(['message' => 'RH non trouvé'], 404);
    }

    $this->authorize('view', $user);

    return response()->json([
        'id'           => $user->id,
        'email'        => $user->email,
        'last_name'    => $user->last_name,
        'first_name'   => $user->first_name,
        'active'       => $user->active,
        'date_of_hire' => $user->date_of_hire
            ? \Carbon\Carbon::parse($user->date_of_hire)->format('d/m/Y')
            : null,
        'phone_number' => $user->phone_number,
        'role'         => $user->role ? $user->role->name : null,
    ], 200);
}
}