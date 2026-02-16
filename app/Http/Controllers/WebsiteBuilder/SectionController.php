<?php

namespace App\Http\Controllers\WebsiteBuilder;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\RequiresChurch;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    use RequiresChurch;

    public function index()
    {
        $church = $this->getChurchOrFail();
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
        $church = $this->getChurchOrFail();

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
        $church = $this->getChurchOrFail();
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

    public function updateSettings(Request $request)
    {
        $church = $this->getChurchOrFail();

        $validated = $request->validate([
            'section_id' => 'required|string',
            'settings' => 'required|array',
        ]);

        $allSettings = $church->getPublicSiteSetting('section_settings', []);
        $allSettings[$validated['section_id']] = $validated['settings'];
        $church->setPublicSiteSetting('section_settings', $allSettings);

        return response()->json(['success' => true, 'message' => 'Налаштування секції оновлено']);
    }
}
