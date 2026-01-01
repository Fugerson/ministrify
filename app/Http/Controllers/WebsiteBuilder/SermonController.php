<?php

namespace App\Http\Controllers\WebsiteBuilder;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\RequiresChurch;
use App\Models\Sermon;
use App\Models\SermonSeries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SermonController extends Controller
{
    public function index()
    {
        $church = $this->getChurchOrFail();
        $sermons = $church->sermons()->with('series', 'speaker')->latest('sermon_date')->paginate(20);
        $series = $church->sermonSeries()->orderBy('sort_order')->get();

        return view('website-builder.sermons.index', compact('church', 'sermons', 'series'));
    }

    public function create()
    {
        $church = $this->getChurchOrFail();
        $series = $church->sermonSeries()->orderBy('sort_order')->get();
        $speakers = $church->staffMembers()->orderBy('name')->get();

        return view('website-builder.sermons.create', compact('church', 'series', 'speakers'));
    }

    public function store(Request $request)
    {
        $church = $this->getChurchOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'sermon_series_id' => 'nullable|exists:sermon_series,id',
            'speaker_id' => 'nullable|exists:staff_members,id',
            'speaker_name' => 'nullable|string|max:255',
            'sermon_date' => 'required|date',
            'video_url' => 'nullable|url|max:500',
            'audio_url' => 'nullable|url|max:500',
            'audio_file' => 'nullable|file|mimes:mp3,wav,m4a|max:51200',
            'thumbnail' => 'nullable|image|max:2048',
            'scripture_reference' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'is_featured' => 'boolean',
            'is_public' => 'boolean',
        ]);

        if ($request->hasFile('audio_file')) {
            $validated['audio_file'] = $request->file('audio_file')->store("churches/{$church->id}/sermons/audio", 'public');
        }

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store("churches/{$church->id}/sermons/thumbnails", 'public');
        }

        $validated['church_id'] = $church->id;

        Sermon::create($validated);

        return redirect()->route('website-builder.sermons.index')->with('success', 'Проповідь додано');
    }

    public function edit(Sermon $sermon)
    {
        $this->authorize('view', $sermon);
        $church = $this->getChurchOrFail();
        $series = $church->sermonSeries()->orderBy('sort_order')->get();
        $speakers = $church->staffMembers()->orderBy('name')->get();

        return view('website-builder.sermons.edit', compact('church', 'sermon', 'series', 'speakers'));
    }

    public function update(Request $request, Sermon $sermon)
    {
        $this->authorize('update', $sermon);
        $church = $this->getChurchOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'sermon_series_id' => 'nullable|exists:sermon_series,id',
            'speaker_id' => 'nullable|exists:staff_members,id',
            'speaker_name' => 'nullable|string|max:255',
            'sermon_date' => 'required|date',
            'video_url' => 'nullable|url|max:500',
            'audio_url' => 'nullable|url|max:500',
            'audio_file' => 'nullable|file|mimes:mp3,wav,m4a|max:51200',
            'thumbnail' => 'nullable|image|max:2048',
            'scripture_reference' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'is_featured' => 'boolean',
            'is_public' => 'boolean',
        ]);

        if ($request->hasFile('audio_file')) {
            if ($sermon->audio_file) {
                Storage::disk('public')->delete($sermon->audio_file);
            }
            $validated['audio_file'] = $request->file('audio_file')->store("churches/{$church->id}/sermons/audio", 'public');
        }

        if ($request->hasFile('thumbnail')) {
            if ($sermon->thumbnail) {
                Storage::disk('public')->delete($sermon->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store("churches/{$church->id}/sermons/thumbnails", 'public');
        }

        $sermon->update($validated);

        return redirect()->route('website-builder.sermons.index')->with('success', 'Проповідь оновлено');
    }

    public function destroy(Sermon $sermon)
    {
        $this->authorize('delete', $sermon);

        if ($sermon->audio_file) {
            Storage::disk('public')->delete($sermon->audio_file);
        }
        if ($sermon->thumbnail) {
            Storage::disk('public')->delete($sermon->thumbnail);
        }

        $sermon->delete();

        return redirect()->route('website-builder.sermons.index')->with('success', 'Проповідь видалено');
    }

    // Series management
    public function seriesIndex()
    {
        $church = $this->getChurchOrFail();
        $series = $church->sermonSeries()->withCount('sermons')->orderBy('sort_order')->get();

        return view('website-builder.sermons.series.index', compact('church', 'series'));
    }

    public function seriesStore(Request $request)
    {
        $church = $this->getChurchOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'thumbnail' => 'nullable|image|max:2048',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store("churches/{$church->id}/sermons/series", 'public');
        }

        $validated['church_id'] = $church->id;
        $validated['sort_order'] = $church->sermonSeries()->max('sort_order') + 1;

        SermonSeries::create($validated);

        return redirect()->route('website-builder.sermons.series.index')->with('success', 'Серію проповідей створено');
    }

    public function seriesUpdate(Request $request, SermonSeries $series)
    {
        $this->authorize('update', $series);
        $church = $this->getChurchOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'thumbnail' => 'nullable|image|max:2048',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($series->thumbnail) {
                Storage::disk('public')->delete($series->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store("churches/{$church->id}/sermons/series", 'public');
        }

        $series->update($validated);

        return redirect()->route('website-builder.sermons.series.index')->with('success', 'Серію оновлено');
    }

    public function seriesDestroy(SermonSeries $series)
    {
        $this->authorize('delete', $series);

        if ($series->thumbnail) {
            Storage::disk('public')->delete($series->thumbnail);
        }

        // Detach sermons from series (don't delete them)
        $series->sermons()->update(['sermon_series_id' => null]);
        $series->delete();

        return redirect()->route('website-builder.sermons.series.index')->with('success', 'Серію видалено');
    }
}
