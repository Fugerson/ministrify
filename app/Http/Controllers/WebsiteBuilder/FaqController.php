<?php

namespace App\Http\Controllers\WebsiteBuilder;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\RequiresChurch;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    use RequiresChurch;

    public function index()
    {
        $church = $this->getChurchOrFail();
        $faqs = $church->faqs()->orderBy('category')->orderBy('sort_order')->get();

        // Group by category
        $faqsByCategory = $faqs->groupBy('category');

        return view('website-builder.faq.index', compact('church', 'faqs', 'faqsByCategory'));
    }

    public function store(Request $request)
    {
        $church = $this->getChurchOrFail();

        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string|max:5000',
            'category' => 'nullable|string|max:100',
            'is_public' => 'boolean',
        ]);

        $validated['church_id'] = $church->id;
        $validated['sort_order'] = $church->faqs()->max('sort_order') + 1;

        Faq::create($validated);

        return back()->with('success', 'FAQ додано');
    }

    public function update(Request $request, Faq $faq)
    {
        $this->authorize('update', $faq);

        $validated = $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string|max:5000',
            'category' => 'nullable|string|max:100',
            'is_public' => 'boolean',
        ]);

        $faq->update($validated);

        return back()->with('success', 'FAQ оновлено');
    }

    public function destroy(Faq $faq)
    {
        $this->authorize('delete', $faq);
        $faq->delete();

        return back()->with('success', 'FAQ видалено');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:faqs,id',
        ]);

        foreach ($validated['order'] as $index => $id) {
            Faq::where('id', $id)
                ->where('church_id', auth()->user()->church_id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
