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

        // Save to user preferences if authenticated
        if (auth()->check()) {
            $prefs = auth()->user()->preferences ?? [];
            $prefs['locale'] = $locale;
            auth()->user()->update(['preferences' => $prefs]);
        }

        // Store in session for this browser session
        session(['app.locale' => $locale]);

        // Also set persistent cookie as fallback
        return redirect()->back()
            ->cookie(
                'locale',
                $locale,
                60 * 24 * 365,      // 1 year
                '/',
                null,
                config('app.env') === 'production',
                false
            )
            ->with('locale_changed', true);
    }
}
