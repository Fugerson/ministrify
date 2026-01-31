<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserPreferencesController extends Controller
{
    public function updateTheme(Request $request)
    {
        $validated = $request->validate([
            'theme' => 'required|in:light,dark,auto',
        ]);

        auth()->user()->update(['theme' => $validated['theme']]);

        return response()->json(['success' => true, 'theme' => $validated['theme']]);
    }

    public function updatePreferences(Request $request)
    {
        $user = auth()->user();
        $preferences = $user->preferences ?? [];

        if ($request->has('sidebar_collapsed')) {
            $preferences['sidebar_collapsed'] = $request->boolean('sidebar_collapsed');
        }

        if ($request->has('dashboard_widgets')) {
            $preferences['dashboard_widgets'] = $request->input('dashboard_widgets');
        }

        $user->update(['preferences' => $preferences]);

        return response()->json(['success' => true]);
    }

    public function completeOnboarding()
    {
        auth()->user()->update(['onboarding_completed' => true]);

        return response()->json(['success' => true]);
    }
}
