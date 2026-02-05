<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // 1️⃣ Vérifier utilisateur connecté
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Non authentifié'
            ], 401);
        }

        // 2️⃣ Récupérer l’utilisateur
        $user = Auth::user();

        // 3️⃣ Vérifier le rôle
        if (!in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'Accès refusé'
            ], 403);
        }

        // 4️⃣ Autoriser l’accès
        return $next($request);
    }
}
