<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Song;
use App\Models\ServicePlanItem;
use Illuminate\Http\Request;

class MusicStandController extends Controller
{
    /**
     * Display upcoming services with songs for the musician
     */
    public function index()
    {
        $church = $this->getCurrentChurch();
        $person = auth()->user()->person;

        // Get upcoming services with worship items
        $upcomingServices = Event::where('church_id', $church->id)
            ->where('is_service', true)
            ->where('date', '>=', now()->startOfDay())
            ->whereHas('planItems', function ($q) {
                $q->where('type', ServicePlanItem::TYPE_WORSHIP);
            })
            ->with(['ministry', 'planItems' => function ($q) {
                $q->where('type', ServicePlanItem::TYPE_WORSHIP)->ordered();
            }])
            ->orderBy('date')
            ->orderBy('time')
            ->take(10)
            ->get();

        // Get all songs for quick reference
        $allSongs = Song::where('church_id', $church->id)
            ->orderBy('title')
            ->get(['id', 'title', 'artist', 'key']);

        return view('music-stand.index', compact('upcomingServices', 'allSongs'));
    }

    /**
     * Display songs for a specific service
     */
    public function show(Event $event)
    {
        $church = $this->getCurrentChurch();

        // Verify event belongs to church
        if ($event->church_id !== $church->id) {
            abort(403);
        }

        // Get worship items with song details
        $worshipItems = $event->planItems()
            ->where('type', ServicePlanItem::TYPE_WORSHIP)
            ->ordered()
            ->get();

        // Parse song IDs from worship item titles/notes and load songs
        $songIds = [];
        foreach ($worshipItems as $item) {
            // Check if title contains song reference [song:ID]
            if (preg_match('/\[song:(\d+)\]/', $item->notes ?? '', $matches)) {
                $songIds[] = $matches[1];
            }
        }

        $songs = Song::where('church_id', $church->id)
            ->whereIn('id', $songIds)
            ->get()
            ->keyBy('id');

        return view('music-stand.show', compact('event', 'worshipItems', 'songs'));
    }

    /**
     * Display a single song in presentation mode
     */
    public function song(Event $event, Song $song, Request $request)
    {
        $church = $this->getCurrentChurch();

        // Verify belongs to church
        if ($event->church_id !== $church->id || $song->church_id !== $church->id) {
            abort(403);
        }

        // Get transpose key from request
        $transposeKey = $request->get('key', $song->key);

        // Get chords (transposed if needed)
        $chords = $song->chords;
        if ($transposeKey && $transposeKey !== $song->key) {
            $chords = $song->transposeChords($song->key, $transposeKey);
        }

        // Get all worship songs for this event for navigation
        $worshipItems = $event->planItems()
            ->where('type', ServicePlanItem::TYPE_WORSHIP)
            ->ordered()
            ->get();

        return view('music-stand.song', compact('event', 'song', 'chords', 'transposeKey', 'worshipItems'));
    }

    /**
     * API: Get song data with transposed chords
     */
    public function songData(Song $song, Request $request)
    {
        $church = $this->getCurrentChurch();

        if ($song->church_id !== $church->id) {
            abort(403);
        }

        $transposeKey = $request->get('key', $song->key);

        $chords = $song->chords;
        if ($transposeKey && $transposeKey !== $song->key) {
            $chords = $song->transposeChords($song->key, $transposeKey);
        }

        // Convert chords to HTML
        $chordsHtml = '';
        if ($chords) {
            $chordsHtml = preg_replace(
                '/\[([A-G][#b]?m?(?:add|sus|dim|aug|maj|min)?[0-9]?(?:\/[A-G][#b]?)?)\]/',
                '<span class="chord">$1</span>',
                e($chords)
            );
            $chordsHtml = nl2br($chordsHtml);
        }

        return response()->json([
            'id' => $song->id,
            'title' => $song->title,
            'artist' => $song->artist,
            'key' => $song->key,
            'transposeKey' => $transposeKey,
            'bpm' => $song->bpm,
            'chords' => $chords,
            'chordsHtml' => $chordsHtml,
            'lyrics' => $song->lyrics,
        ]);
    }
}
