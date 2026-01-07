<?php

namespace App\Http\Middleware;

use App\Models\RolePermission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
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

        // Super admin bypasses all checks
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Admin role bypasses most checks (configurable per church)
        $churchId = $user->church_id;
        if (!$churchId) {
            abort(403, 'Церква не знайдена');
        }

        if (!RolePermission::hasPermission($churchId, $user->role, $module, $action)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Недостатньо прав доступу'], 403);
            }

            abort(403, 'Недостатньо прав для виконання цієї дії');
        }

        return $next($request);
    }
}
