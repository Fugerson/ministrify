<?php

namespace App\Http\Controllers\WebsiteBuilder;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestimonialController extends Controller
{
    public function index()
    {
        $church = auth()->user()->church;
        $testimonials = $church->testimonials()->orderBy('sort_order')->get();

        return view('website-builder.testimonials.index', compact('church', 'testimonials'));
    }

    public function store(Request $request)
    {
        $church = auth()->user()->church;

        $validated = $request->validate([
            'author_name' => 'required|string|max:255',
            'author_role' => 'nullable|string|max:255',
            'content' => 'required|string|max:2000',
            'photo' => 'nullable|image|max:2048',
            'video_url' => 'nullable|url|max:500',
            'rating' => 'nullable|integer|min:1|max:5',
            'is_featured' => 'boolean',
            'is_public' => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store("churches/{$church->id}/testimonials", 'public');
        }

        $validated['church_id'] = $church->id;
        $validated['sort_order'] = $church->testimonials()->max('sort_order') + 1;

        Testimonial::create($validated);

        return back()->with('success', 'Свідчення додано');
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $this->authorize('update', $testimonial);
        $church = auth()->user()->church;

        $validated = $request->validate([
            'author_name' => 'required|string|max:255',
            'author_role' => 'nullable|string|max:255',
            'content' => 'required|string|max:2000',
            'photo' => 'nullable|image|max:2048',
            'video_url' => 'nullable|url|max:500',
            'rating' => 'nullable|integer|min:1|max:5',
            'is_featured' => 'boolean',
            'is_public' => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            if ($testimonial->photo) {
                Storage::disk('public')->delete($testimonial->photo);
            }
            $validated['photo'] = $request->file('photo')->store("churches/{$church->id}/testimonials", 'public');
        }

        $testimonial->update($validated);

        return back()->with('success', 'Свідчення оновлено');
    }

    public function destroy(Testimonial $testimonial)
    {
        $this->authorize('delete', $testimonial);

        if ($testimonial->photo) {
            Storage::disk('public')->delete($testimonial->photo);
        }

        $testimonial->delete();

        return back()->with('success', 'Свідчення видалено');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:testimonials,id',
        ]);

        foreach ($validated['order'] as $index => $id) {
            Testimonial::where('id', $id)
                ->where('church_id', auth()->user()->church_id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
