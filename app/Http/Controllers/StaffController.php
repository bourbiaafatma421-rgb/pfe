<?php

namespace App\Http\Controllers;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    /**
     * Lister les staffs (manager + RH)
     */
    public function index()
    {
        $staffs = Staff::with('user')->get();
        return response()->json($staffs);
    }

    // Ajouter un staff (manager ou RH)
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'role' => 'required|in:manager,rh',
        ]);

        // Vérifier qu'on n'a pas déjà ce rôle
        $existe = Staff::where('role', $request->role)->exists();
        if ($existe) {
            return response()->json([
                'message' => 'Ce rôle existe déjà'
            ], 409);
        }

        // Créer l'utilisateur correspondant
        $password = Str::random(8);
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($password),
            'role' => strtoupper($request->role), // MANAGER ou RH
            'active' => 1,
        ]);

        // Créer le staff
        $staff = Staff::create([
            'user_id' => $user->id,
            'role' => $request->role,
            'nom' => $request->nom,
            'prenom' => $request->prenom,
        ]);

        return response()->json([
            'message' => "Staff ({$request->role}) ajouté avec succès",
            'staff' => $staff,
            'user' => [
                'email' => $user->email,
                'password_temporaire' => $password
            ]
        ], 201);
    }

    // Supprimer un staff
    public function destroy($id)
    {
        $staff = Staff::findOrFail($id);
        $staff->delete();

        return response()->json([
            'message' => 'Staff supprimé avec succès'
        ]);
    }
}
