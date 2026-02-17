<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $this->resolveLocale($request);

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $available = config('app.available_locales', ['uk', 'en']);

        // 1. Session (highest priority — explicit user choice in current session)
        $sessionLocale = session('app.locale');
        if ($sessionLocale && in_array($sessionLocale, $available)) {
            return $sessionLocale;
        }

        // 2. Cookie (explicit user choice, persistent)
        $cookie = $request->cookie('locale');
        if ($cookie && in_array($cookie, $available)) {
            return $cookie;
        }

        // 3. Authenticated user preference (from database)
        if ($request->user()) {
            $userLocale = $request->user()->preferences['locale'] ?? null;
            if ($userLocale && in_array($userLocale, $available)) {
                return $userLocale;
            }
        }

        // 4. Default
        return config('app.locale', 'uk');
    }
}
