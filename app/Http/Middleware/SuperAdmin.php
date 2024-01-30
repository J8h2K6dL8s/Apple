<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifier si l'utilisateur est un superadmin
        if ($request->user() && $request->user()->type === 'superadmin') {
            return $next($request);
        }

        // Retourner une réponse JSON non autorisée si l'utilisateur n'est pas un superadmin
        return response()->json(['error' => 'Unauthorized. Vous n\'avez pas les autorisations nécessaires.']);
    }
}
