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



class CollaborateurController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected $service;
    public function __construct(CollaborateurService $service){
        $this->service = $service;
    }
    // Créer un collaborateur
    public function ajouter(CollaborateurRequestRules $request){
        $this->authorize('create',Collaborateur::class);
        $result = $this->service->createCollaborateur($request->validated());
        return response()->json([
            'message' => 'Collaborateur créé avec succès',
            'email' => $result['email'],
            'password_temporaire' => $result['password']
        ], 201);
    }
    //get
    public function index(Request $request){
    $this->authorize('viewAny',Collaborateur::class);
    $collab = $this->service->getCollaborateurs($request->all());
    return response()->json($collab);
}
   //modifier
   public function modifiercollaborateur(ModifierCollaborateurRequest $request, Collaborateur $collaborateur){
    $this->authorize('update', $collaborateur);
    $user = auth()->guard()->user();
    $collaborateur=$this->service->updatecollaborateur($collaborateur,$request->validated(),$user);
    return response()->json([
        'message' => 'Collaborateur modifié avec succès',
        'collaborateur' => $collaborateur
    ]);
}
}
