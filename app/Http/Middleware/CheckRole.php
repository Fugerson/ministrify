<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Admin always has access
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Check if user has one of the required roles
        if (!empty($roles) && !$user->hasRole($roles)) {
            abort(403, 'У вас немає доступу до цієї сторінки.');
        }

        return $next($request);
    }
}
