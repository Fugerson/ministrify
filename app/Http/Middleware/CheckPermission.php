<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     * Uses ChurchRole permissions with fallback to legacy RolePermission
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $module
     * @param  string  $action
     */
    public function handle(Request $request, Closure $next, string $module, string $action = 'view'): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        // Check permission using User model (handles church roles + legacy fallback)
        if (!$user->hasPermission($module, $action)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Недостатньо прав доступу'], 403);
            }

            abort(403, 'Недостатньо прав для виконання цієї дії');
        }

        return $next($request);
    }
}
