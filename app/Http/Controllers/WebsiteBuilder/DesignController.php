<?php

namespace App\Http\Controllers\WebsiteBuilder;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DesignController extends Controller
{
    public function index()
    {
        $church = auth()->user()->church;
        $fonts = config('public_site_templates.fonts', []);
        $buttonStyles = config('public_site_templates.button_styles', []);
        $heroTypes = config('public_site_templates.hero_types', []);
        $navigationStyles = config('public_site_templates.navigation_styles', []);

        return view('website-builder.design.index', compact(
            'church', 'fonts', 'buttonStyles', 'heroTypes', 'navigationStyles'
        ));
    }

    public function updateColors(Request $request)
    {
        $church = auth()->user()->church;

        $validated = $request->validate([
            'primary' => 'nullable|string|max:7',
            'secondary' => 'nullable|string|max:7',
            'accent' => 'nullable|string|max:7',
            'background' => 'nullable|string|max:7',
            'text' => 'nullable|string|max:7',
            'heading' => 'nullable|string|max:7',
        ]);

        // Also update main primary_color
        if (!empty($validated['primary'])) {
            $church->primary_color = $validated['primary'];
        }

        $church->setPublicSiteSetting('colors', array_filter($validated));

        return back()->with('success', 'Кольори оновлено');
    }

    public function updateFonts(Request $request)
    {
        $church = auth()->user()->church;
        $availableFonts = array_keys(config('public_site_templates.fonts', []));

        $validated = $request->validate([
            'heading' => 'nullable|string|in:' . implode(',', $availableFonts),
            'body' => 'nullable|string|in:' . implode(',', $availableFonts),
        ]);

        $church->setPublicSiteSetting('fonts', array_filter($validated));

        return back()->with('success', 'Шрифти оновлено');
    }

    public function updateHero(Request $request)
    {
        $church = auth()->user()->church;

        $validated = $request->validate([
            'type' => 'nullable|string|in:image,video,gradient,slideshow,split',
            'overlay_opacity' => 'nullable|integer|min:0|max:100',
            'text_alignment' => 'nullable|string|in:left,center,right',
            'show_cta' => 'boolean',
            'title' => 'nullable|string|max:200',
            'subtitle' => 'nullable|string|max:500',
            'cta_text' => 'nullable|string|max:100',
            'cta_url' => 'nullable|string|max:255',
            'video_url' => 'nullable|url|max:255',
        ]);

        $heroSettings = $church->getPublicSiteSetting('hero', []);
        $heroSettings = array_merge($heroSettings, array_filter($validated, fn($v) => $v !== null));

        $church->setPublicSiteSetting('hero', $heroSettings);

        return back()->with('success', 'Hero секцію оновлено');
    }

    public function updateNavigation(Request $request)
    {
        $church = auth()->user()->church;

        $validated = $request->validate([
            'style' => 'nullable|string|in:transparent,solid,minimal',
            'sticky' => 'boolean',
            'show_logo' => 'boolean',
            'show_social' => 'boolean',
        ]);

        $church->setPublicSiteSetting('navigation', $validated);

        return back()->with('success', 'Навігацію оновлено');
    }

    public function updateFooter(Request $request)
    {
        $church = auth()->user()->church;

        $validated = $request->validate([
            'style' => 'nullable|string|in:simple,centered,multi-column',
            'show_social' => 'boolean',
            'show_contact' => 'boolean',
            'copyright_text' => 'nullable|string|max:500',
        ]);

        $church->setPublicSiteSetting('footer', $validated);

        return back()->with('success', 'Футер оновлено');
    }

    public function updateCustomCss(Request $request)
    {
        $church = auth()->user()->church;

        $validated = $request->validate([
            'custom_css' => 'nullable|string|max:50000',
        ]);

        $church->setPublicSiteSetting('custom_css', $validated['custom_css']);

        return back()->with('success', 'Кастомний CSS збережено');
    }

    public function uploadHeroImage(Request $request)
    {
        $request->validate([
            'hero_image' => 'required|image|max:5120', // 5MB max
        ]);

        $church = auth()->user()->church;
        $path = $request->file('hero_image')->store("churches/{$church->id}/hero", 'public');

        $heroSettings = $church->getPublicSiteSetting('hero', []);
        $heroSettings['image'] = $path;
        $church->setPublicSiteSetting('hero', $heroSettings);

        return back()->with('success', 'Hero зображення завантажено');
    }

    public function uploadHeroSlide(Request $request)
    {
        $request->validate([
            'slide_image' => 'required|image|max:5120',
        ]);

        $church = auth()->user()->church;
        $path = $request->file('slide_image')->store("churches/{$church->id}/hero/slides", 'public');

        $heroSettings = $church->getPublicSiteSetting('hero', []);
        $slides = $heroSettings['slides'] ?? [];
        $slides[] = ['image' => $path];
        $heroSettings['slides'] = $slides;
        $church->setPublicSiteSetting('hero', $heroSettings);

        return back()->with('success', 'Слайд додано');
    }

    public function deleteHeroSlide(Request $request, int $index)
    {
        $church = auth()->user()->church;
        $heroSettings = $church->getPublicSiteSetting('hero', []);
        $slides = $heroSettings['slides'] ?? [];

        if (isset($slides[$index])) {
            if (!empty($slides[$index]['image'])) {
                Storage::disk('public')->delete($slides[$index]['image']);
            }
            unset($slides[$index]);
            $heroSettings['slides'] = array_values($slides);
            $church->setPublicSiteSetting('hero', $heroSettings);
        }

        return back()->with('success', 'Слайд видалено');
    }
}
