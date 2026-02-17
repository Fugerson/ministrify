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

        // 1. Cookie (highest priority — explicit user choice)
        $cookie = $request->cookie('locale');
        \Log::debug('SetLocale: Checking cookie', [
            'cookie_value' => $cookie,
            'all_cookies' => $request->cookies->all(),
        ]);
        if ($cookie && in_array($cookie, $available)) {
            \Log::debug('SetLocale: Using cookie', ['locale' => $cookie]);
            return $cookie;
        }

        // 2. Authenticated user preference
        if ($request->user()) {
            $userLocale = $request->user()->preferences['locale'] ?? null;
            if ($userLocale && in_array($userLocale, $available)) {
                \Log::debug('SetLocale: Using user preference', ['locale' => $userLocale]);
                return $userLocale;
            }
        }

        // 3. Default
        \Log::debug('SetLocale: Using default locale');
        return config('app.locale', 'uk');
    }
}
