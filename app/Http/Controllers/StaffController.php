<?php

namespace App\Http\Controllers;

use App\Http\Requests\AjoutStaffRequest;
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
    public function store(AjoutStaffRequest $request)
    {
     $data = $request->validated();

    // Vérifier qu'on n'a pas déjà ce rôle
    if (Staff::where('role', $data['role'])->exists()) {
        return response()->json([
            'message' => 'Ce rôle existe déjà'
        ], 409);
    }

    // Mot de passe temporaire
    $password = Str::random(8);

    // Création utilisateur
    $user = User::create([
        'email' => $data['email'],
        'password' => Hash::make($password),
        'role' => $data['role'],
        'active' => 1,
    ]);

    // Création staff
    Staff::create([
        'user_id' => $user->id,
        'role' => $data['role'],
        'nom' => $data['nom'],
        'prenom' => $data['prenom'],
    ]);

    return response()->json([
        'message' => "Staff ({$data['role']}) ajouté avec succès",
        'user' => [
            'email' => $user->email,
            'password_temporaire' => $password
        ]
    ], 201);
}
}