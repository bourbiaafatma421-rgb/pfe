<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; 

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  mixed  ...$roles

     */
    public function handle(Request $request, Closure $next, ...$roles)
        {

            $user = $request->user();

            if (!$user) {
                return response()->json(['message' => 'Non authentifié'], 401);
            }

            if (!in_array(strtolower($user->role->nom ?? ''), array_map('strtolower', $roles))) {
                return response()->json(['message' => 'Accès refusé'], 403);
            }

            // L'utilisateur a le bon rôle
            return $next($request);
        }
}
