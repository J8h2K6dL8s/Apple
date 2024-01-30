<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;
use Auth;
class Authenticate extends  Middleware
{
    public function handle($request, Closure $next, ...$guards)
    { 
        $user = auth('sanctum')->user() ;
        if ($user) {
            return $next($request);
        } 
           return response()->json(['error' => 'Unauthenticated.'], 401);
    }

}