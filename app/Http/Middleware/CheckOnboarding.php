<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOnboarding
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Skip for guests
        if (!$user) {
            return $next($request);
        }

        // Skip for non-admins (volunteers and leaders don't need onboarding)
        if (!$user->isAdmin()) {
            return $next($request);
        }

        // Skip for super admins
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Skip if already completed
        if ($user->onboarding_completed) {
            return $next($request);
        }

        // Skip if already on onboarding routes
        if ($request->is('onboarding*')) {
            return $next($request);
        }

        // Skip for API requests
        if ($request->expectsJson()) {
            return $next($request);
        }

        // Skip for specific routes that should always be accessible
        $allowedRoutes = [
            'logout',
            'two-factor*',
            'preferences.*',
        ];

        foreach ($allowedRoutes as $pattern) {
            if ($request->is($pattern) || $request->routeIs($pattern)) {
                return $next($request);
            }
        }

        // Redirect to onboarding
        return redirect()->route('onboarding.show');
    }
}
