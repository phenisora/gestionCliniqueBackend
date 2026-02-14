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
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. On récupère l'utilisateur connecté via JWT
        $user = auth('api')->user();

        // 2. On vérifie s'il est connecté ET si son rôle correspond
        if (!$user || $user->role !== $role) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé. Cette action nécessite le rôle : ' . $role,
                'votre_role' => $user ? $user->role : 'non authentifié'
            ], 403); // 403 est le code HTTP pour "Forbidden" (Interdit)
        }

        return $next($request);
    }
}
