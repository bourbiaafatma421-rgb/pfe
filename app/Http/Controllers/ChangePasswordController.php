<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
//use Illuminate\Support\Facades\Auth;

class ChangePasswordController extends Controller
{
    public function setPassword(ChangePasswordRequest $request)
    {
        $user = $request->user();

        
        if ($user->password_changed) {
            return response()->json([
                'message' => 'Le mot de passe a déjà été défini'
            ], 403);
        }

        $user->password = Hash::make($request->new_password);
        $user->password_changed = true;
        $user->save();

        //$user->tokens()->delete();
        
        return response()->json([
            'message' => 'Mot de passe défini avec succès',
        ], 200);
    }
}
