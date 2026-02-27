<?php

namespace App\Http\Controllers\Collaborateur;

use App\Http\Requests\CollaborateurRequestRules;
use App\Http\Requests\ModifierCollaborateurRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\CollaborateurService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;



class CollaborateurController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected $service;

    public function __construct(CollaborateurService $service){
        $this->service = $service;
    }
      public function ajouter(CollaborateurRequestRules $request)
{
        $this->authorize('create', User::class); 

    try {
        $result = $this->service->createCollaborateur($request->validated());

        return response()->json([
            'message' => 'Collaborateur créé avec succès',
            'email' => $result['user']->email,
            'password_temporaire' => $result['password'],
        ], 201);

    } catch (\Exception $e) {
        // Gestion d’erreurs spécifique pour email déjà existant
        if (str_contains($e->getMessage(), 'users_email_unique')) {
            return response()->json([
                'message' => 'Cet email existe déjà.',
                'details' => $e->getMessage()
            ], 409);
        }
        // Gestion générique avec détails
        return response()->json([
            'message' => 'Erreur serveur',
            'details' => $e->getMessage(),
            'trace' => config('app.debug') ? $e->getTraceAsString() : null
        ], 500);
    }
}
     // Lister les collaborateurs
    
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $collaborateurs = $this->service->getCollaborateurs($request->all());

        return response()->json([
            'message' => 'Collaborateurs récupérés avec succès',
            'collaborateurs' => $collaborateurs,
        ], 200);
    }

    // Modifier un collaborateur
    public function modifiercollaborateur(ModifierCollaborateurRequest $request, User $collaborateur)
    {
        $this->authorize('update', $collaborateur);

        $user = auth()->guard()->user();
        $updated = $this->service->updateCollaborateur($collaborateur, $request->validated(), $user);

        return response()->json([
            'message' => 'Collaborateur modifié avec succès',
            'collaborateur' => $updated,
        ], 200);
    }
    public function getMonProfil(){
        $profil = $this->service->getMonProfil();

        return response()->json([
            'message' => 'Profil récupéré avec succès',
            'collaborateur' => $profil,
        ], 200);
    }
}
