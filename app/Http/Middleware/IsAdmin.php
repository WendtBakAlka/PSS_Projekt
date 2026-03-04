<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Sprawdź, czy użytkownik jest zalogowany i jest adminem
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403, 'Brak dostępu');
        }

        return $next($request);
    }
}
