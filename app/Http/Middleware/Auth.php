<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Auth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ...$guards)
    { 
        $user = auth('sanctum')->user() ;
   
        if ($user) {
            return $next($request);
        } 
           return response()->json(['error' => 'Utilisateur non connecté.'], 401);
    }
}
