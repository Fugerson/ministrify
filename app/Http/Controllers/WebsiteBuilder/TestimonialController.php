<?php

namespace App\Http\Controllers\WebsiteBuilder;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\RequiresChurch;
use App\Models\Testimonial;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestimonialController extends Controller
{
    use RequiresChurch;

    public function index()
    {
        $church = $this->getChurchOrFail();
        $testimonials = $church->testimonials()->orderBy('sort_order')->get();

        return view('website-builder.testimonials.index', compact('church', 'testimonials'));
    }

    public function store(Request $request)
    {
        $church = $this->getChurchOrFail();

        $validated = $request->validate([
            'author_name' => 'required|string|max:255',
            'author_role' => 'nullable|string|max:255',
            'content' => 'required|string|max:2000',
            'photo' => 'nullable|mimes:jpg,jpeg,png,gif,webp,heic,heif|max:2048',
            'video_url' => 'nullable|url|max:500',
            'rating' => 'nullable|integer|min:1|max:5',
            'is_featured' => 'boolean',
            'is_public' => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            $stored = ImageService::storeWithHeicConversion($request->file('photo'), "churches/{$church->id}/testimonials");
            $validated['photo'] = $stored['path'];
        }

        $validated['church_id'] = $church->id;
        $validated['sort_order'] = $church->testimonials()->max('sort_order') + 1;

        Testimonial::create($validated);

        return back()->with('success', 'Свідчення додано');
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $this->authorize('update', $testimonial);
        $church = $this->getChurchOrFail();

        $validated = $request->validate([
            'author_name' => 'required|string|max:255',
            'author_role' => 'nullable|string|max:255',
            'content' => 'required|string|max:2000',
            'photo' => 'nullable|mimes:jpg,jpeg,png,gif,webp,heic,heif|max:2048',
            'video_url' => 'nullable|url|max:500',
            'rating' => 'nullable|integer|min:1|max:5',
            'is_featured' => 'boolean',
            'is_public' => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            if ($testimonial->photo) {
                Storage::disk('public')->delete($testimonial->photo);
            }
            $stored = ImageService::storeWithHeicConversion($request->file('photo'), "churches/{$church->id}/testimonials");
            $validated['photo'] = $stored['path'];
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
        $church = $this->getChurchOrFail();

        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:testimonials,id',
        ]);

        foreach ($validated['order'] as $index => $id) {
            Testimonial::where('id', $id)
                ->where('church_id', $church->id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
