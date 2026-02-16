<?php

namespace App\Http\Controllers;

use App\Http\Requests\AjoutStaffRequest;
use App\Http\Requests\ModifierStaffRequest;
use App\Http\Requests\AjoutManagerRequest;
use App\Models\User;
use App\Services\StaffService;
use Illuminate\Http\Request;

// Exceptions personnalisées
use App\Exceptions\Staff\StaffNotFoundException;
use App\Exceptions\Staff\StaffUpdateForbiddenException;
use App\Exceptions\Staff\ManagerAlreadyExistsException;
use App\Exceptions\Staff\InvalidRHException;


class StaffController extends Controller
{
    
     protected StaffService $service;

    public function __construct(StaffService $service)
    {
        $this->service = $service;
    }

    // Lister tous les RH
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $users = $this->service->listRH($request->only(['nom', 'prenom', 'active']));

        return response()->json($users);
    }

    // Ajouter un RH
    public function store(AjoutStaffRequest $request)
    {
        $this->authorize('create', User::class);

        try {
            $result = $this->service->createRH($request->validated());

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
            $updatedUser = $this->service->updateRH($user, $request->validated(), auth()->user());

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
}


