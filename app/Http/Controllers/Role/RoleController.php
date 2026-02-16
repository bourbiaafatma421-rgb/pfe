<?php

namespace App\Http\Controllers\Role;

use App\Exceptions\Role\RoleExistsException as RoleExistsException;
use App\Exceptions\Role\RoleHasUsersException as RoleHasUsersException;
use App\Exceptions\Role\RoleProtectedException as RoleProtectedException;
use App\Http\Requests\RequestValidationRole;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class RoleController extends BaseController
{
    protected $service;
    public function __construct(RoleService $service){
        $this->service = $service;
    }
    public function ajouter(RequestValidationRole $request)
    {
        $this->authorize('create', Role::class);

        try {
            $role = $this->service->createRole($request->validated());
            return response()->json([
                'message' => 'Rôle créé avec succès',
                'role' => $role
            ], 201);

        } catch (RoleExistsException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur'], 500);
        }
    }

    // Récupérer tous les rôles (seulement nom)
    public function getall()
    {
        $this->authorize('viewAny', Role::class);
        $roles = $this->service->getRoles();
        return response()->json([
            'message' => 'Rôles récupérés avec succès',
            'roles' => $roles
        ], 200);
    }

    // Modifier un rôle
    public function modifier(RequestValidationRole $request, $id)
    {
        $this->authorize('update', Role::class);

        try {
            $role = $this->service->updateRole($id, $request->validated());
            return response()->json([
                'message' => 'Rôle mis à jour avec succès',
                'role' => $role
            ], 200);

        } catch (RoleProtectedException $e) {
            return response()->json(['message' => $e->getMessage()], 403);

        } catch (RoleExistsException $e) {
            return response()->json(['message' => $e->getMessage()], 409);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur'], 500);
        }
    }

    // Supprimer un rôle
    public function supprimer($id)
    {
        $this->authorize('delete', Role::class);
        try {
            $this->service->deleteRole($id);
            return response()->json(['message' => 'Rôle supprimé avec succès'], 200);
        } catch (RoleProtectedException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (RoleHasUsersException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'users' => $e->users
            ], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'trace'=>$e->getTraceAsString()], 500);
        }
    }
}