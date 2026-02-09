<?php

namespace App\Http\Controllers;

use App\Http\Requests\CollaborateurRequestRules;
use App\Http\Requests\ModifierCollaborateurRequest;
use App\Models\Collaborateur;
use Illuminate\Http\Request;
use App\Services\CollaborateurService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;



class CollaborateurController extends BaseController{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected $service;
    public function __construct(CollaborateurService $service)
    {
        $this->service = $service;
    }
    // Créer un collaborateur
    public function ajouter(CollaborateurRequestRules $request)
    {

        DB::beginTransaction();
        try{
        $password = Str::random(8);
        $result = $this->service->createCollaborateur($request->validated());
        return response()->json([
            'message' => 'Collaborateur créé avec succès',
            'email' => $result['email'],
            'password_temporaire' => $result['password']
        ], 201);
    }
   public function index(Request $request)
{
    $query = Collaborateur::with('user');

    if ($request->nom) {
        $query->where('nom', $request->nom);
    }

    if ($request->prenom) {
        $query->where('prenom', $request->prenom);
    }

    if ($request->etat) {
        $query->where('etat', $request->etat);
    }

    $collab = $query->paginate(10)->items();

    return response()->json($collab);
}


   public function modifiercollaborateur(ModifierCollaborateurRequest $request, Collaborateur $collaborateur)
{
    $this->authorize('update', $collaborateur);

    $user = auth()->guard()->user();
    if ($user->role === 'rh') {
        $collaborateur->update($request->only(['poste', 'numero_telephone', 'etat']));
        
    } elseif ($user->role === 'collaborateur' && $user->id === $collaborateur->user_id) {
        $collaborateur->update($request->only(['numero_telephone']));
    }

    return response()->json([
        'message' => 'Collaborateur modifié avec succès',
        'collaborateur' => $collaborateur
    ]);
}
}

