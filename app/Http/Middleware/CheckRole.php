<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
     public function handle(Request $request, Closure $next, ...$roles)
    {
        // Vérifie si l'utilisateur est connecté
       if (!Auth::check()) {
            return response()->json(['message' => 'Non authentifié'], 401);
        }
        //vérifie si le compte activer ou non 
        $user = Auth::user();
        if (isset($user->active) && !$user->active) {
            return response()->json(['message' => 'Compte désactivé'], 403);
        }

        // Vérifie le rôle
        if (!in_array($user->role, $roles)) {
            return response()->json(['message' => 'Accès interdit'], 403);
        }


        // Passe la requête au prochain middleware ou contrôleur
        return $next($request);
    }
}
