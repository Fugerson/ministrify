<?php

namespace App\Http\Controllers\WebsiteBuilder;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\RequiresChurch;
use App\Models\Gallery;
use App\Models\GalleryPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index()
    {
        $church = $this->getChurchOrFail();
        $galleries = $church->galleries()->withCount('photos')->orderBy('sort_order')->get();

        return view('website-builder.gallery.index', compact('church', 'galleries'));
    }

    public function create()
    {
        $church = $this->getChurchOrFail();
        return view('website-builder.gallery.create', compact('church'));
    }

    public function store(Request $request)
    {
        $church = $this->getChurchOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'event_date' => 'nullable|date',
            'cover_photo' => 'nullable|image|max:2048',
            'is_public' => 'boolean',
        ]);

        if ($request->hasFile('cover_photo')) {
            $validated['cover_photo'] = $request->file('cover_photo')->store("churches/{$church->id}/galleries", 'public');
        }

        $validated['church_id'] = $church->id;
        $validated['sort_order'] = $church->galleries()->max('sort_order') + 1;

        $gallery = Gallery::create($validated);

        return redirect()->route('website-builder.gallery.show', $gallery)->with('success', 'Альбом створено');
    }

    public function show(Gallery $gallery)
    {
        $this->authorize('view', $gallery);
        $church = $this->getChurchOrFail();
        $photos = $gallery->photos()->orderBy('sort_order')->get();

        return view('website-builder.gallery.show', compact('church', 'gallery', 'photos'));
    }

    public function edit(Gallery $gallery)
    {
        $this->authorize('update', $gallery);
        $church = $this->getChurchOrFail();

        return view('website-builder.gallery.edit', compact('church', 'gallery'));
    }

    public function update(Request $request, Gallery $gallery)
    {
        $this->authorize('update', $gallery);
        $church = $this->getChurchOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'event_date' => 'nullable|date',
            'cover_photo' => 'nullable|image|max:2048',
            'is_public' => 'boolean',
        ]);

        if ($request->hasFile('cover_photo')) {
            if ($gallery->cover_photo) {
                Storage::disk('public')->delete($gallery->cover_photo);
            }
            $validated['cover_photo'] = $request->file('cover_photo')->store("churches/{$church->id}/galleries", 'public');
        }

        $gallery->update($validated);

        return redirect()->route('website-builder.gallery.show', $gallery)->with('success', 'Альбом оновлено');
    }

    public function destroy(Gallery $gallery)
    {
        $this->authorize('delete', $gallery);

        // Delete all photos in gallery
        foreach ($gallery->photos as $photo) {
            if ($photo->file_path) {
                Storage::disk('public')->delete($photo->file_path);
            }
            if ($photo->thumbnail_path) {
                Storage::disk('public')->delete($photo->thumbnail_path);
            }
        }

        if ($gallery->cover_photo) {
            Storage::disk('public')->delete($gallery->cover_photo);
        }

        $gallery->photos()->delete();
        $gallery->delete();

        return redirect()->route('website-builder.gallery.index')->with('success', 'Альбом видалено');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:galleries,id',
        ]);

        foreach ($validated['order'] as $index => $id) {
            Gallery::where('id', $id)
                ->where('church_id', auth()->user()->church_id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    // Photo management
    public function uploadPhotos(Request $request, Gallery $gallery)
    {
        $this->authorize('update', $gallery);

        $request->validate([
            'photos' => 'required|array|max:50',
            'photos.*' => 'image|max:5120', // 5MB per photo
        ]);

        $church = $this->getChurchOrFail();
        $maxOrder = $gallery->photos()->max('sort_order') ?? 0;

        foreach ($request->file('photos') as $index => $file) {
            $path = $file->store("churches/{$church->id}/galleries/{$gallery->id}", 'public');

            GalleryPhoto::create([
                'gallery_id' => $gallery->id,
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'sort_order' => $maxOrder + $index + 1,
            ]);
        }

        return back()->with('success', 'Фото завантажено');
    }

    public function updatePhoto(Request $request, GalleryPhoto $photo)
    {
        $this->authorize('update', $photo->gallery);

        $validated = $request->validate([
            'caption' => 'nullable|string|max:500',
            'alt_text' => 'nullable|string|max:255',
        ]);

        $photo->update($validated);

        return back()->with('success', 'Опис фото оновлено');
    }

    public function deletePhoto(GalleryPhoto $photo)
    {
        $this->authorize('update', $photo->gallery);

        if ($photo->file_path) {
            Storage::disk('public')->delete($photo->file_path);
        }
        if ($photo->thumbnail_path) {
            Storage::disk('public')->delete($photo->thumbnail_path);
        }

        $photo->delete();

        return back()->with('success', 'Фото видалено');
    }

    public function reorderPhotos(Request $request, Gallery $gallery)
    {
        $this->authorize('update', $gallery);

        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:gallery_photos,id',
        ]);

        foreach ($validated['order'] as $index => $id) {
            GalleryPhoto::where('id', $id)
                ->where('gallery_id', $gallery->id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    public function setCover(Gallery $gallery, GalleryPhoto $photo)
    {
        $this->authorize('update', $gallery);

        $gallery->update(['cover_photo' => $photo->file_path]);

        return back()->with('success', 'Обкладинку оновлено');
    }
}
