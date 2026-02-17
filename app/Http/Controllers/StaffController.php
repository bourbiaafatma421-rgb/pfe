<?php

namespace App\Http\Controllers;

use App\Http\Requests\AjoutStaffRequest;
use App\Http\Requests\ModifierStaffRequest;
use App\Http\Requests\AjoutManagerRequest;
<<<<<<< HEAD
use App\Models\User;
use App\Services\StaffService;
use Illuminate\Http\Request;

// Exceptions personnalisées
use App\Exceptions\Staff\StaffNotFoundException;
use App\Exceptions\Staff\StaffUpdateForbiddenException;
use App\Exceptions\Staff\ManagerAlreadyExistsException;
use App\Exceptions\Staff\InvalidRHException;

=======
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
>>>>>>> origin/main

class StaffController extends Controller
{
    
<<<<<<< HEAD
     protected StaffService $service;

    public function __construct(StaffService $service)
    {
        $this->service = $service;
    }

    // Lister tous les RH
=======
    // Lister tous les staffs (manager + RH)

>>>>>>> origin/main
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

<<<<<<< HEAD
        $users = $this->service->listRH($request->only(['first_name', 'last_name', 'active']));

        return response()->json($users);
    }

    // Ajouter un RH
=======
        // On commence par tous les RH
        $query = User::with('role')
        ->whereHas('role', function($lerole) {
            $lerole->where('name', 'rh');
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
                'role' => $user->role ? $user->role->name
                 : null,
            ];
        });
        return response()->json($usersFormatted, 200);
    }

    public function toggleActive($id)
    {
        $user = User::find($id);
        $this->authorize('update', $user);
        if (!$user) {
            return response()->json(['message' => 'RH non trouvé'], 404);
        }

        $rhId = Role::where('name', 'rh')->value('id');
        if ($user->role_id != $rhId) {
            return response()->json(['message' => 'L\'utilisateur doit être un RH'], 403);
        }

        $user->active = !$user->active;
        $user->save();

        return response()->json([
            'message' => $user->active ? 'Compte activé avec succès' : 'Compte désactivé avec succès',
            'active' => $user->active
        ]);
    }

    //Ajouter un RH
     
>>>>>>> origin/main
    public function store(AjoutStaffRequest $request)
    {
        $this->authorize('create', User::class);

        try {
<<<<<<< HEAD
            $result = $this->service->createRH($request->validated());
=======
            $this->authorize('create', User::class);
            $data = $request->validated();

            $password = Str::random(8);
            $role = Role::firstOrCreate(['name' => 'rh']);

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
>>>>>>> origin/main

            return response()->json([
                'message' => 'RH ajouté avec succès',
                'user' => [
                    'email' => $result['user']->email,
                    'password_temporaire' => $result['password_temporaire']
                ]
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du RH',
                'erreur' => $e->getMessage()
            ], 500);
        }
    }

    // Modifier un RH
    public function update(ModifierStaffRequest $request, User $user)
    {
        $this->authorize('update', $user);

        try {
<<<<<<< HEAD
            $updatedUser = $this->service->updateRH($user, $request->validated(), auth()->user());
=======
            // Vérifie si le staff existe
            $user = User::with('role')->find($id);
            if (!$user) {
                return response()->json([
                    'message' => 'Staff non trouvé'
                ], 404);
            }

            $currentUser = Auth::user();
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

            if (strtoupper($currentUser->role->name) === 'RH') {
                if ($currentUser->id !== $user->id) {
                    return response()->json([
                        'message' => "Accès refusé : un RH ne peut modifier que son propre profil"
                    ], 403);
                }
            } elseif (strtoupper($currentUser->role->name) === 'MANAGER') {
                $rhRole = Role::whereRaw('LOWER(name) = ?', ['rh'])->first();
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
>>>>>>> origin/main

            return response()->json([
                'message' => 'Staff modifié avec succès',
                'staff' => $updatedUser
            ]);

        } catch (StaffUpdateForbiddenException | InvalidRHException $e) {
            return response()->json(['message' => $e->getMessage()], 403);

        } catch (StaffNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du RH',
                'erreur' => $e->getMessage()
            ], 500);
        }
    }

<<<<<<< HEAD
    // Activer / désactiver un compte RH
    public function toggleActive($id)
    {
        try {
            $user = User::findOrFail($id);
            $this->authorize('toggleActive', $user);
            $updatedUser = $this->service->toggleActiveRH($user);

            return response()->json([
                'message' => $updatedUser->active ? 'Compte activé' : 'Compte désactivé',
                'active' => $updatedUser->active
            ]);

        } catch (InvalidRHException $e) {
            return response()->json(['message' => $e->getMessage()], 403);

        } catch (StaffNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors de l’opération',
                'erreur' => $e->getMessage()
            ], 500);
        }
    }

    // Ajouter un Manager
    public function storeManager(AjoutManagerRequest $request)
    {
        try {
            $result = $this->service->createManager($request->validated());

            return response()->json([
                'message' => 'Manager créé avec succès',
                'password_temporaire' => $result['password_temporaire']
            ], 201);

        } catch (ManagerAlreadyExistsException $e) {
            return response()->json(['message' => $e->getMessage()], 409);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du Manager',
                'erreur' => $e->getMessage()
            ], 500);
        }
    }    
=======
   public function storeManager(AjoutManagerRequest $request)
{
    // Normaliser le nom du rôle en minuscules
    $roleName = 'manager';

    // Cherche le rôle existant, peu importe la casse
    $managerRole = Role::whereRaw('LOWER(name) = ?', [strtolower($roleName)])->first();

    // Si le rôle n'existe pas, le créer
    if (!$managerRole) {
        $managerRole = Role::create(['name' => $roleName]);
    }
    // Vérifie si un utilisateur avec ce rôle existe déjà
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
        'nom' => $data['nom'],
        'prenom' => $data['prenom'],
        'role_id' => $managerRole->id,
        'active' => 1,
        'date_recrutement' => $data['date_recrutement'],
        'numero_telephone' => $data['numero_telephone'],
    ]);

    return response()->json([
        'message' => 'Manager créé avec succès',
        'password_temporaire' => $password
    ], 201);
}

    
>>>>>>> origin/main
}


