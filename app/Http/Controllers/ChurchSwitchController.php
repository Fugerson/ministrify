<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChurchSwitchController extends Controller
{
    public function switch(Request $request)
    {
        $request->validate([
            'church_id' => 'required|integer|exists:churches,id',
        ]);

        $user = auth()->user();

        if (!$user->belongsToChurch($request->church_id)) {
            abort(403, 'Ви не є членом цієї церкви.');
        }

        $user->switchToChurch($request->church_id);

        return redirect()->route('dashboard')
            ->with('success', 'Церкву переключено.');
    }
}
