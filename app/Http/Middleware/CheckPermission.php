<?php

namespace App\Http\Middleware;

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

        if (!$user->hasPermission($module, $action)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => __('messages.insufficient_permissions')], 403);
            }

            abort(403, __('messages.insufficient_permissions_action'));
        }

        return $next($request);
    }
}
