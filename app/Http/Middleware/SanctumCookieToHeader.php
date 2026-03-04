<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanctumCookieToHeader
{
    public function handle(Request $request, Closure $next)
    {
        // Jeśli request ma cookie 'sanctum_token' ale nie ma header Authorization
        if (!$request->bearerToken() && $token = $request->cookie('sanctum_token')) {
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        return $next($request);
    }
}