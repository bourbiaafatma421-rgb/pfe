<?php

namespace App\Http\Controllers;

use App\Http\Requests\CollaborateurRequestRules;
use App\Http\Requests\ModifierCollaborateurRequest;
use App\Models\Collaborateur;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CollaborateurController extends Controller
{
    // Créer un collaborateur
    public function ajouter(CollaborateurRequestRules $request)
    {
        $password = Str::random(8);

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($password),
            'role' => 'collaborateur',
        ]);

        $collab = Collaborateur::create([
            'user_id' => $user->id,
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'numero_telephone' => $request->numero_telephone,
            'poste' => $request->poste,
            'etat' => $request->etat ?? 'encours',
        ]);

        return response()->json([
            'message' => 'Collaborateur créé avec succès',
            'collaborateur' => $collab,
            'user' => [
                'email' => $user->email,
                'password_temporaire' => $password
            ]
        ], 201);
    }

    // Rechercher par nom et prénom
    public function getbynometprenom(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
        ]);

        $collab = Collaborateur::with('user')
                                ->where('nom', $request->nom)
                                ->where('prenom', $request->prenom)
                                ->get();

        if ($collab->isEmpty()) {
            return response()->json(['message' => 'Collaborateur non trouvé'], 404);
        }

        return response()->json($collab);
    }

    // Rechercher par état
    public function getbyetat(Request $request)
    {
        $request->validate([
            'etat' => 'required|string',
        ]);

    $collab = Collaborateur::with('user')
                            ->where('etat', $request->etat)
                            ->get();

        if ($collab->isEmpty()) {
            return response()->json(['message' => 'Collaborateur non trouvé'], 404);
        }

        return response()->json($collab);
    }

    // Lister tous les collaborateurs
    public function getall()
    {
        $collab = Collaborateur::with('user')->get();
        if ($collab->isEmpty()) {
            return response()->json(['message' => 'Collaborateur non trouvé'], 404);
        }

        return response()->json($collab);
    }

    // Modifier un collaborateur
    public function modifiercollaborateur(ModifierCollaborateurRequest $request, $id)
    {
        $collab = Collaborateur::find($id);

        if (!$collab) {
            return response()->json(['message' => 'Collaborateur non trouvé'], 404);
        }

        $collab->fill($request->only(['poste', 'numero_telephone', 'etat']));
        $collab->save();

        return response()->json([
            'message' => 'Collaborateur modifié avec succès',
            'collaborateur' => $collab
        ]);
    }
}
