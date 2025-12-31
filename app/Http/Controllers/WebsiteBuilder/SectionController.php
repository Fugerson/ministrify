<?php

namespace App\Http\Controllers\WebsiteBuilder;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function index()
    {
        $church = auth()->user()->church;
        $availableSections = config('public_site_templates.sections', []);
        $currentSections = $church->getPublicSiteSetting('sections', $church->getDefaultSections());

        // Merge available sections with current settings
        $sections = collect($currentSections)
            ->map(function ($section) use ($availableSections) {
                $sectionConfig = $availableSections[$section['id']] ?? [];
                return array_merge($section, $sectionConfig);
            })
            ->sortBy('order')
            ->values();

        return view('website-builder.sections.index', compact('church', 'sections', 'availableSections'));
    }

    public function update(Request $request)
    {
        $church = auth()->user()->church;

        $validated = $request->validate([
            'sections' => 'required|array',
            'sections.*.id' => 'required|string',
            'sections.*.enabled' => 'boolean',
            'sections.*.order' => 'integer',
        ]);

        $sections = collect($validated['sections'])
            ->map(function ($section, $index) {
                return [
                    'id' => $section['id'],
                    'enabled' => $section['enabled'] ?? false,
                    'order' => $section['order'] ?? $index,
                ];
            })
            ->sortBy('order')
            ->values()
            ->all();

        $church->setPublicSiteSetting('sections', $sections);

        return response()->json(['success' => true, 'message' => 'Секції оновлено']);
    }

    public function toggle(Request $request, string $sectionId)
    {
        $church = auth()->user()->church;
        $sections = $church->getPublicSiteSetting('sections', $church->getDefaultSections());

        $sections = collect($sections)
            ->map(function ($section) use ($sectionId, $request) {
                if ($section['id'] === $sectionId) {
                    $section['enabled'] = $request->boolean('enabled', !($section['enabled'] ?? false));
                }
                return $section;
            })
            ->all();

        $church->setPublicSiteSetting('sections', $sections);

        return back()->with('success', 'Статус секції оновлено');
    }
}
