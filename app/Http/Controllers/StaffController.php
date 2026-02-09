<?php

namespace App\Http\Controllers;

use App\Http\Requests\AjoutStaffRequest;
use App\Http\Requests\ModifierStaffRequest;
use App\Http\Requests\DeleteStaffRequest;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\AjoutManagerRequest;

class StaffController extends Controller
{
    
    // Lister tous les staffs (manager + RH)
     
    public function index()
    {
        $this->authorize('viewAny',Staff::class);
        $staffs = Staff::select('id', 'user_id', 'role', 'nom', 'prenom')
            ->with(['user:id,email,role,active,password_changed'])
            ->get();

        return response()->json($staffs);
    }

    
    //Ajouter un RH
     
        public function store(AjoutStaffRequest $request)
    {
        $this->authorize('create',Staff::class);
        $data = $request->validated();

        $password = Str::random(8);

        $user = User::create([
            'email' => $data['email'],
            'password' => Hash::make($password),
            'role' => "rh", 
            'active' => 1,
        ]);

        $staff = Staff::create([
            'user_id' => $user->id,
            'role' => "rh",
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
        ]);

        return response()->json([
            'message' => "RH ajouté avec succès",
            'user' => [
                'email' => $user->email,
                'password_temporaire' => $password
            ]
        ], 201);
    }

    
     //Modifier un staff existant
     
    public function update(ModifierStaffRequest $request, $id)
    {
        $staff = Staff::find($id);
        if(!$staff){
            return response()->json(['message'=>'Staff non trouvé'],404);
        }
        $this->authorize('update',$staff);
        $data=$request->validated();
        $staff->update([
            'nom' => $data['nom'],
            'prenom' => $data['prenom']
        ]);
        if ($staff->user) {
            $staff->user->update([
                'email' => $data['email'],
                'role' => $data['role'],
                'active' => $data['active'] ?? $staff->user->active,
            ]);
        }
        return response()->json([
            'message'=>'Staff modifié avec succés',
            'staff'=>$staff,
        ]);
    }
    
    //Supprimer un staff(rh)
     
    public function destroy(DeleteStaffRequest $request, $id)
    {
        $staff = Staff::findOrFail($id);
        $this->authorize('delete',$staff);
        $staff->user()->delete();
        $staff->delete();
         

        return response()->json([
            'message' => "Staff supprimé avec succès"
        ], 200);
    }
    //ajouter un seul manager
    public function storeManager(AjoutManagerRequest $request)
    {
        $this->authorize('create',Staff::class);
        if (Staff::where('role', 'manager')->exists()) {
            return response()->json([
                'message' => 'Un manager existe déjà'
            ], 409);
        }

        $data = $request->validated();
        $password = Str::random(10);

        $user = User::create([
            'email' => $data['email'],
            'password' => Hash::make($password),
            'role' => 'manager',
            'active' => 1,
        ]);

        Staff::create([
            'user_id' => $user->id,
            'role' => 'manager',
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
        ]);

        return response()->json([
            'message' => 'Manager créé avec succès',
            'password_temporaire' => $password
        ], 201);
    }

}