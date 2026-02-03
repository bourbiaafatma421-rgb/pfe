<?php

namespace App\Http\Controllers;

use App\Models\Collaborateur;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CollaborateurController extends Controller
{
    //cree un collaborateur 
    public function ajouter(Request $request){
        $request->validate([
            'email'=>'required|email|unique:users,email',
            'nom'=>'required|string',
            'prenom'=>'required|string',
            'numero_telephone'=>['required','string','regex:/^\+\d{2,3}[0-9]{6,10}$/'],
            'poste'=>'required|string',
            'etat'=>'required|string|in:encours,terminer',
            ],
            ['numero_telephone.regex' => 'Le numéro doit commencer par un indicatif international, ex: +21612345678']);
        $password=Str::random(8);
        $user=User::create([
            'email'=>$request->email,
            'password'=>Hash::make($password),
            'role'=>'collaborateur',
            
        ]);
        $collab=Collaborateur::create([
            'user_id'=>$user->id,
            'nom'=>$request->nom,
            'prenom'=>$request->prenom,
            'numero_telephone'=>$request->numero_telephone,
            'poste'=>$request->poste,
            'etat'=>$request->etat ?? 'encours',
        ]);
        //retourner email+password 
        return response()->json([
            'message'=>'Collaborateur créé avec succès',
            'collaborateur'=>$collab,
            'user'=>[
                'email'=>$user->email,
                'password_temporaire'=>$password
            ]
        ],201);
    }
    //get
    public function getbynometprenom(Request $request){
        $request->validate([
            'nom'=>'required|string',
            'prenom'=>'required|string',
        ]);
        $collab=Collaborateur::join('users', 'collaborateurs.user_id', '=', 'users.id')
                             ->where('nom',$request->nom)
                             ->where('prenom',$request->prenom)
                             ->select('collaborateurs.*', 'users.email')
                             ->get();
        if (!$collab) {
        return response()->json([
            'message' => 'Collaborateur non trouvé'
        ], 404);
    }

    return response()->json($collab);
}
public function getbyetat(Request $request){
    $request->validate([
        'etat'=>'required|string',
    ]);
    $collab=Collaborateur::join('users', 'collaborateurs.user_id', '=', 'users.id')
                         ->where('etat',$request->etat)
                          ->select('collaborateurs.*', 'users.email')
                          ->get();
    if (!$collab) {
        return response()->json([
            'message' => 'Collaborateur non trouvé'
        ], 404);
}else{
    return response()->json($collab);
}
}
public function getall(){
    $collab = Collaborateur::join('users', 'collaborateurs.user_id', '=', 'users.id')
                            ->select('collaborateurs.*', 'users.email')
                            ->get();
    if(!$collab){
        return response()->json()([
            'message'=>'collaborateur non trouvé'
        ],404);
    }else{
        return response()->json($collab);
    }
}

}