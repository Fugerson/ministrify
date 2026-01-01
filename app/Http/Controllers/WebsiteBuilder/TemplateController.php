<?php

namespace App\Http\Controllers\WebsiteBuilder;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\RequiresChurch;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index()
    {
        $church = $this->getChurchOrFail();
        $templates = config('public_site_templates.templates', []);
        $currentTemplate = $church->active_template;

        return view('website-builder.templates.index', compact('church', 'templates', 'currentTemplate'));
    }

    public function apply(Request $request, string $template)
    {
        $church = $this->getChurchOrFail();
        $templates = config('public_site_templates.templates', []);

        if (!isset($templates[$template])) {
            return back()->with('error', 'Невідомий шаблон');
        }

        $church->public_template = $template;
        $church->save();

        return back()->with('success', "Шаблон \"{$templates[$template]['name']}\" застосовано!");
    }
}
