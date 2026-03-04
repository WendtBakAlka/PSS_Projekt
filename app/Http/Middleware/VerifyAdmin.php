<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Sprawdź czy użytkownik jest zalogowany i jest adminem
        if (!auth()->check() || !auth()->user()->is_admin) {
            
            // Dla API (żądania JSON/AJAX) - zwróć JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthorized. Administrator access required.',
                    'error' => 'Forbidden',
                    'user_is_admin' => auth()->check() ? auth()->user()->is_admin : false
                ], 403);
            }
            
            // Dla web (Blade) - przekieruj z komunikatem
            return redirect('/games')
                ->with('error', 'Brak dostępu: tylko administratorzy mają uprawnienia.');
        }

        return $next($request);
    }
}