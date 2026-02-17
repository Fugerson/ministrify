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

        // Set locale immediately for this response
        app()->setLocale($locale);

        // Return with cookie
        return redirect()->back()
            ->cookie('locale', $locale, 60*24*365, '/', null, false, false) // 1 year, non-secure, non-httpOnly
            ->with('locale_changed', true);
    }
}
