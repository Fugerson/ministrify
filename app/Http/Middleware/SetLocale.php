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

        // 1. Cookie (set by JavaScript when user clicks language button)
        $cookie = $request->cookie('locale');
        if ($cookie && in_array($cookie, $available)) {
            return $cookie;
        }

        // 2. Authenticated user preference (from database, for logged-in users)
        if ($request->user()) {
            $userLocale = $request->user()->preferences['locale'] ?? null;
            if ($userLocale && in_array($userLocale, $available)) {
                return $userLocale;
            }
        }

        // 3. Default
        return config('app.locale', 'uk');
    }
}
