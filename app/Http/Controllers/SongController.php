<?php

namespace App\Http\Controllers;

use App\Models\Song;
use App\Models\Event;
use Illuminate\Http\Request;

class SongController extends Controller
{
    public function index(Request $request)
    {
        $church = $this->getCurrentChurch();

        $query = Song::where('church_id', $church->id)
            ->search($request->get('search'))
            ->withKey($request->get('key'))
            ->withTag($request->get('tag'));

        // Sort
        $sort = $request->get('sort', 'title');
        if ($sort === 'recent') {
            $query->latest();
        } elseif ($sort === 'popular') {
            $query->orderByDesc('times_used');
        } elseif ($sort === 'last_used') {
            $query->orderByDesc('last_used_at');
        } else {
            $query->orderBy('title');
        }

        $songs = $query->paginate(24);

        // Get all unique tags for filter
        $allTags = Song::where('church_id', $church->id)
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        return view('songs.index', compact('songs', 'allTags'));
    }

    public function create()
    {
        return view('songs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'nullable|string|max:255',
            'key' => 'nullable|string|max:10',
            'bpm' => 'nullable|integer|min:30|max:300',
            'lyrics' => 'nullable|string|max:10000',
            'chords' => 'nullable|string|max:20000',
            'ccli_number' => 'nullable|string|max:50',
            'youtube_url' => 'nullable|url|max:255',
            'spotify_url' => 'nullable|url|max:255',
            'tags' => 'nullable|string|max:500',
        ]);

        $church = $this->getCurrentChurch();

        // Parse tags from comma-separated string
        $tags = null;
        if (!empty($validated['tags'])) {
            $tags = array_map('trim', explode(',', $validated['tags']));
            $tags = array_filter($tags);
        }

        Song::create([
            'church_id' => $church->id,
            'title' => $validated['title'],
            'artist' => $validated['artist'],
            'key' => $validated['key'],
            'bpm' => $validated['bpm'],
            'lyrics' => $validated['lyrics'],
            'chords' => $validated['chords'],
            'ccli_number' => $validated['ccli_number'],
            'youtube_url' => $validated['youtube_url'],
            'spotify_url' => $validated['spotify_url'],
            'tags' => $tags,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('songs.index')
            ->with('success', 'Пісню додано до бібліотеки.');
    }

    public function show(Song $song)
    {
        $this->authorizeChurch($song);

        $song->load(['creator', 'events' => fn($q) => $q->latest('date')->limit(10)]);

        return view('songs.show', compact('song'));
    }

    public function edit(Song $song)
    {
        $this->authorizeChurch($song);

        return view('songs.edit', compact('song'));
    }

    public function update(Request $request, Song $song)
    {
        $this->authorizeChurch($song);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'nullable|string|max:255',
            'key' => 'nullable|string|max:10',
            'bpm' => 'nullable|integer|min:30|max:300',
            'lyrics' => 'nullable|string|max:10000',
            'chords' => 'nullable|string|max:20000',
            'ccli_number' => 'nullable|string|max:50',
            'youtube_url' => 'nullable|url|max:255',
            'spotify_url' => 'nullable|url|max:255',
            'tags' => 'nullable|string|max:500',
        ]);

        $tags = null;
        if (!empty($validated['tags'])) {
            $tags = array_map('trim', explode(',', $validated['tags']));
            $tags = array_filter($tags);
        }

        $song->update([
            'title' => $validated['title'],
            'artist' => $validated['artist'],
            'key' => $validated['key'],
            'bpm' => $validated['bpm'],
            'lyrics' => $validated['lyrics'],
            'chords' => $validated['chords'],
            'ccli_number' => $validated['ccli_number'],
            'youtube_url' => $validated['youtube_url'],
            'spotify_url' => $validated['spotify_url'],
            'tags' => $tags,
        ]);

        return redirect()->route('songs.show', $song)
            ->with('success', 'Пісню оновлено.');
    }

    public function destroy(Song $song)
    {
        $this->authorizeChurch($song);

        $song->delete();

        return redirect()->route('songs.index')
            ->with('success', 'Пісню видалено.');
    }

    // Add song to event setlist
    public function addToEvent(Request $request, Song $song)
    {
        $this->authorizeChurch($song);

        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'key' => 'nullable|string|max:10',
        ]);

        $event = Event::findOrFail($validated['event_id']);
        $this->authorizeChurch($event);

        // Get max order
        $maxOrder = $event->songs()->max('order') ?? 0;

        $event->songs()->attach($song->id, [
            'order' => $maxOrder + 1,
            'key' => $validated['key'] ?? $song->key,
        ]);

        $song->incrementUsage();

        return back()->with('success', 'Пісню додано до події.');
    }

    // Event setlist management
    public function eventSetlist(Event $event)
    {
        $this->authorizeChurch($event);

        $songs = $event->songs()->orderBy('order')->get();
        $church = $this->getCurrentChurch();
        $availableSongs = Song::where('church_id', $church->id)
            ->whereNotIn('id', $songs->pluck('id'))
            ->orderBy('title')
            ->get();

        return view('songs.setlist', compact('event', 'songs', 'availableSongs'));
    }

    public function updateSetlistOrder(Request $request, Event $event)
    {
        $this->authorizeChurch($event);

        $validated = $request->validate([
            'songs' => 'required|array',
            'songs.*' => 'exists:songs,id',
        ]);

        foreach ($validated['songs'] as $order => $songId) {
            $event->songs()->updateExistingPivot($songId, ['order' => $order + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function removeFromEvent(Event $event, Song $song)
    {
        $this->authorizeChurch($event);

        $event->songs()->detach($song->id);

        return back()->with('success', 'Пісню видалено з події.');
    }
}
