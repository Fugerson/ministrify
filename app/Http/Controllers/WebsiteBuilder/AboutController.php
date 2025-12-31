<?php

namespace App\Http\Controllers\WebsiteBuilder;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    public function index()
    {
        $church = auth()->user()->church;
        $aboutContent = $church->about_content;

        return view('website-builder.about.index', compact('church', 'aboutContent'));
    }

    public function update(Request $request)
    {
        $church = auth()->user()->church;

        $validated = $request->validate([
            'mission' => 'nullable|string|max:2000',
            'vision' => 'nullable|string|max:2000',
            'values' => 'nullable|array',
            'values.*' => 'nullable|string|max:500',
            'history' => 'nullable|string|max:10000',
            'beliefs' => 'nullable|string|max:10000',
        ]);

        // Filter empty values
        if (isset($validated['values'])) {
            $validated['values'] = array_filter($validated['values']);
        }

        $church->setPublicSiteSetting('about', $validated);

        return back()->with('success', 'Розділ "Про нас" оновлено');
    }
}
