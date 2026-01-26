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

        // Load all songs for client-side filtering
        $songs = Song::where('church_id', $church->id)
            ->orderBy('title')
            ->get();

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
        $church = $this->getCurrentChurch();

        // Get existing artists for autocomplete
        $artists = Song::where('church_id', $church->id)
            ->whereNotNull('artist')
            ->distinct()
            ->pluck('artist')
            ->sort()
            ->values();

        // Get all existing tags
        $allTags = Song::where('church_id', $church->id)
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        return view('songs.create', compact('artists', 'allTags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'nullable|string|max:255',
            'key' => 'nullable|string|max:10',
            'bpm' => 'nullable|numeric|min:30|max:300',
            'lyrics' => 'nullable|string|max:10000',
            'chords' => 'nullable|string|max:20000',
            'ccli_number' => 'nullable|string|max:50',
            'youtube_url' => 'nullable|max:255',
            'spotify_url' => 'nullable|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'new_tag' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:5000',
        ]);

        $church = $this->getCurrentChurch();

        // Clean empty values
        $validated['youtube_url'] = !empty($validated['youtube_url']) ? $validated['youtube_url'] : null;
        $validated['spotify_url'] = !empty($validated['spotify_url']) ? $validated['spotify_url'] : null;
        $validated['bpm'] = !empty($validated['bpm']) ? (int)$validated['bpm'] : null;

        // Combine selected tags with new tag
        $tags = $validated['tags'] ?? [];
        if (!empty($validated['new_tag'])) {
            $newTags = array_map('trim', explode(',', $validated['new_tag']));
            $tags = array_merge($tags, array_filter($newTags));
        }
        $tags = array_unique(array_filter($tags));

        $song = Song::create([
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
            'tags' => !empty($tags) ? array_values($tags) : null,
            'notes' => $validated['notes'],
            'created_by' => auth()->id(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'song' => [
                    'id' => $song->id,
                    'title' => $song->title,
                    'artist' => $song->artist,
                    'key' => $song->key,
                    'bpm' => $song->bpm,
                    'lyrics' => $song->lyrics,
                    'chords' => $song->chords,
                    'ccli_number' => $song->ccli_number,
                    'youtube_url' => $song->youtube_url,
                    'spotify_url' => $song->spotify_url,
                    'tags' => $song->tags ?? [],
                    'notes' => $song->notes,
                    'times_used' => $song->times_used ?? 0,
                    'created_at' => $song->created_at,
                ]
            ]);
        }

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
        $church = $this->getCurrentChurch();

        // Get existing artists for autocomplete
        $artists = Song::where('church_id', $church->id)
            ->whereNotNull('artist')
            ->distinct()
            ->pluck('artist')
            ->sort()
            ->values();

        // Get all existing tags
        $allTags = Song::where('church_id', $church->id)
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        return view('songs.edit', compact('song', 'artists', 'allTags'));
    }

    public function update(Request $request, Song $song)
    {
        $this->authorizeChurch($song);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'nullable|string|max:255',
            'key' => 'nullable|string|max:10',
            'bpm' => 'nullable|numeric|min:30|max:300',
            'lyrics' => 'nullable|string|max:10000',
            'chords' => 'nullable|string|max:20000',
            'ccli_number' => 'nullable|string|max:50',
            'youtube_url' => 'nullable|max:255',
            'spotify_url' => 'nullable|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'new_tag' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:5000',
        ]);

        // Clean empty values
        $validated['youtube_url'] = !empty($validated['youtube_url']) ? $validated['youtube_url'] : null;
        $validated['spotify_url'] = !empty($validated['spotify_url']) ? $validated['spotify_url'] : null;
        $validated['bpm'] = !empty($validated['bpm']) ? (int)$validated['bpm'] : null;

        // Combine selected tags with new tag
        $tags = $validated['tags'] ?? [];
        if (!empty($validated['new_tag'])) {
            $newTags = array_map('trim', explode(',', $validated['new_tag']));
            $tags = array_merge($tags, array_filter($newTags));
        }
        $tags = array_unique(array_filter($tags));

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
            'tags' => !empty($tags) ? array_values($tags) : null,
            'notes' => $validated['notes'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'song' => [
                    'id' => $song->id,
                    'title' => $song->title,
                    'artist' => $song->artist,
                    'key' => $song->key,
                    'bpm' => $song->bpm,
                    'lyrics' => $song->lyrics,
                    'chords' => $song->chords,
                    'ccli_number' => $song->ccli_number,
                    'youtube_url' => $song->youtube_url,
                    'spotify_url' => $song->spotify_url,
                    'tags' => $song->tags ?? [],
                    'notes' => $song->notes,
                    'times_used' => $song->times_used ?? 0,
                    'created_at' => $song->created_at,
                ]
            ]);
        }

        return redirect()->route('songs.show', $song)
            ->with('success', 'Пісню оновлено.');
    }

    public function destroy(Request $request, Song $song)
    {
        $this->authorizeChurch($song);

        $song->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

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

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'order' => $maxOrder + 1]);
        }

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

    public function removeFromEvent(Request $request, Event $event, Song $song)
    {
        $this->authorizeChurch($event);

        $event->songs()->detach($song->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Пісню видалено з події.');
    }
}
