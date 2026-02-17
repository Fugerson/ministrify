<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleSwitchController extends Controller
{
    public function switch(string $locale)
    {
        $available = config('app.available_locales', ['uk', 'en']);
        if (!in_array($locale, $available)) {
            abort(400);
        }

        \Log::info('Switching locale', [
            'from' => app()->getLocale(),
            'to' => $locale,
            'user_id' => auth()->id(),
        ]);

        // Save to user preferences if authenticated
        if (auth()->check()) {
            $prefs = auth()->user()->preferences ?? [];
            $prefs['locale'] = $locale;
            auth()->user()->update(['preferences' => $prefs]);
        }

        // Set locale immediately for this response
        app()->setLocale($locale);

        // Return with unencrypted cookie (locale cookie is excluded from encryption in EncryptCookies middleware)
        $response = redirect()->back()
            ->cookie(
                'locale',           // name
                $locale,            // value - simple string, not encrypted
                60 * 24 * 365,      // minutes (1 year)
                '/',                // path
                null,               // domain (null = current domain)
                config('app.env') === 'production', // secure (HTTPS only in production)
                false,              // httpOnly (allow JS access if needed)
                false,              // raw
                'none'              // sameSite (allow cross-site cookies)
            )
            ->with('locale_changed', true);

        \Log::info('Locale cookie set', [
            'cookie_name' => 'locale',
            'cookie_value' => $locale,
            'response_headers' => $response->headers->all(),
        ]);

        return $response;
    }
}
