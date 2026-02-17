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
        \Log::debug('🔍 SetLocale checking cookie', [
            'cookie_value' => $cookie,
            'all_cookies' => $request->cookies->all(),
            'is_available' => $cookie && in_array($cookie, $available),
        ]);

        if ($cookie && in_array($cookie, $available)) {
            \Log::info('✅ SetLocale using cookie locale: ' . $cookie);
            return $cookie;
        }

        // 2. Authenticated user preference (from database, for logged-in users)
        if ($request->user()) {
            $userLocale = $request->user()->preferences['locale'] ?? null;
            if ($userLocale && in_array($userLocale, $available)) {
                \Log::info('✅ SetLocale using user preference locale: ' . $userLocale);
                return $userLocale;
            }
        }

        // 3. Default
        \Log::info('ℹ️ SetLocale using default locale: uk');
        return config('app.locale', 'uk');
    }
}
