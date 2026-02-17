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

        // Save to user preferences if authenticated (for future sessions)
        if (auth()->check()) {
            $prefs = auth()->user()->preferences ?? [];
            $prefs['locale'] = $locale;
            auth()->user()->update(['preferences' => $prefs]);
        }

        // Cookie is already set by JavaScript, just acknowledge the request
        return response()->json(['status' => 'ok', 'locale' => $locale]);
    }
}
