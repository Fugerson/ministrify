<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOnboarding
{
    public function handle(Request $request, Closure $next): Response
    {
        // Onboarding is now handled by Driver.js guided tour on the dashboard.
        // No more redirects — let all users through.
        return $next($request);
    }
}
