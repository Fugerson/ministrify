<?php

namespace App\Http\Controllers\WebsiteBuilder;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\RequiresChurch;
use Illuminate\Http\Request;

class WebsiteBuilderController extends Controller
{
    use RequiresChurch;

    public function index()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $church = $this->getChurchOrFail();

        $stats = [
            'events_count' => $church->events()->where('is_public', true)->count(),
            'ministries_count' => $church->ministries()->where('is_public', true)->count(),
            'groups_count' => $church->groups()->where('is_public', true)->count(),
            'staff_count' => $church->staffMembers()->where('is_public', true)->count(),
            'sermons_count' => $church->sermons()->where('is_public', true)->count(),
            'galleries_count' => $church->galleries()->where('is_public', true)->count(),
            'blog_posts_count' => $church->blogPosts()->published()->count(),
            'faqs_count' => $church->faqs()->where('is_public', true)->count(),
            'testimonials_count' => $church->testimonials()->where('is_public', true)->count(),
        ];

        $enabledSections = $church->enabled_sections;
        $templateConfig = $church->getTemplateConfig();

        return view('website-builder.index', compact('church', 'stats', 'enabledSections', 'templateConfig'));
    }

    public function preview()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $church = $this->getChurchOrFail();
        return redirect()->route('public.church', $church->slug);
    }
}
