<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventWorshipTeam;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\PersonWorshipSkill;
use App\Models\Song;
use App\Models\WorshipRole;
use Illuminate\Http\Request;

class WorshipTeamController extends Controller
{
    /**
     * Get worship events for a ministry portal
     */
    public function events(Ministry $ministry)
    {
        $this->authorizeChurch($ministry);

        if (!$ministry->is_worship_ministry) {
            abort(404);
        }

        // Get all church events with music
        $events = Event::where('church_id', $this->getCurrentChurch()->id)
            ->where('has_music', true)
            ->where('date', '>=', now()->subDays(7))
            ->with(['songs', 'worshipTeam.person', 'worshipTeam.worshipRole'])
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        $worshipRoles = WorshipRole::where('church_id', $this->getCurrentChurch()->id)
            ->orderBy('sort_order')
            ->get();

        return view('ministries.worship-events', compact('ministry', 'events', 'worshipRoles'));
    }

    /**
     * Show worship statistics
     */
    public function stats(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);

        if (!$ministry->is_worship_ministry) {
            abort(404);
        }

        $period = $request->get('period', 'month');
        $startDate = match($period) {
            'month' => now()->startOfMonth(),
            '3months' => now()->subMonths(3),
            '6months' => now()->subMonths(6),
            'year' => now()->subYear(),
            'all' => null,
            default => now()->startOfMonth(),
        };

        $churchId = $this->getCurrentChurch()->id;

        // Base query for events
        $eventsQuery = Event::where('church_id', $churchId)
            ->where('has_music', true);

        if ($startDate) {
            $eventsQuery->where('date', '>=', $startDate);
        }

        $eventIds = $eventsQuery->pluck('id');

        // Summary stats
        $stats = [
            'total_events' => $eventIds->count(),
            'total_participants' => EventWorshipTeam::whereIn('event_id', $eventIds)
                ->distinct('person_id')
                ->count('person_id'),
            'total_songs' => \DB::table('event_songs')
                ->whereIn('event_id', $eventIds)
                ->count(),
            'unique_songs' => \DB::table('event_songs')
                ->whereIn('event_id', $eventIds)
                ->distinct('song_id')
                ->count('song_id'),
        ];

        // Top participants
        $topParticipants = EventWorshipTeam::whereIn('event_id', $eventIds)
            ->select('person_id', \DB::raw('COUNT(*) as count'))
            ->groupBy('person_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Load person and roles for each participant
        foreach ($topParticipants as $participant) {
            $participant->person = Person::find($participant->person_id);
            $participant->roles = WorshipRole::whereIn('id', function($q) use ($participant, $eventIds) {
                $q->select('worship_role_id')
                    ->from('event_worship_team')
                    ->where('person_id', $participant->person_id)
                    ->whereIn('event_id', $eventIds)
                    ->distinct();
            })->get();
        }

        // Top songs
        $topSongs = Song::where('church_id', $churchId)
            ->whereIn('id', function($q) use ($eventIds) {
                $q->select('song_id')->from('event_songs')->whereIn('event_id', $eventIds);
            })
            ->withCount(['events as play_count' => function($q) use ($eventIds) {
                $q->whereIn('events.id', $eventIds);
            }])
            ->orderByDesc('play_count')
            ->limit(10)
            ->get();

        // Role distribution
        $roleStats = WorshipRole::where('church_id', $churchId)
            ->withCount(['eventTeamMembers as count' => function($q) use ($eventIds) {
                $q->whereIn('event_id', $eventIds);
            }])
            ->orderByDesc('count')
            ->get();

        // Recent events
        $recentEvents = Event::where('church_id', $churchId)
            ->where('has_music', true)
            ->where('date', '<=', now())
            ->withCount(['songs as songs_count', 'worshipTeam as team_count'])
            ->orderByDesc('date')
            ->limit(5)
            ->get();

        return view('ministries.worship-stats', compact(
            'ministry', 'period', 'stats', 'topParticipants', 'topSongs', 'roleStats', 'recentEvents'
        ));
    }

    /**
     * Show worship event details for editing songs and team
     */
    public function eventShow(Ministry $ministry, Event $event)
    {
        $this->authorizeChurch($ministry);
        $this->authorizeChurch($event);

        if (!$ministry->is_worship_ministry || !$event->has_music) {
            abort(404);
        }

        $event->load(['songs', 'worshipTeam.person', 'worshipTeam.worshipRole']);

        $worshipRoles = WorshipRole::where('church_id', $this->getCurrentChurch()->id)
            ->orderBy('sort_order')
            ->get();

        // Get ministry members with their worship skills
        $members = $ministry->members()
            ->with('worshipSkills.worshipRole')
            ->get();

        // Also include the leader
        if ($ministry->leader) {
            $ministry->leader->load('worshipSkills.worshipRole');
            if (!$members->contains('id', $ministry->leader->id)) {
                $members->prepend($ministry->leader);
            }
        }

        // Get all church songs for adding
        $availableSongs = Song::where('church_id', $this->getCurrentChurch()->id)
            ->orderBy('title')
            ->get();

        return view('ministries.worship-event-detail', compact(
            'ministry', 'event', 'worshipRoles', 'members', 'availableSongs'
        ));
    }

    /**
     * Get worship event data as JSON for modal
     */
    public function eventData(Ministry $ministry, Event $event)
    {
        $this->authorizeChurch($ministry);
        $this->authorizeChurch($event);

        if (!$ministry->is_worship_ministry || !$event->has_music) {
            abort(404);
        }

        $event->load(['songs', 'worshipTeam.person', 'worshipTeam.worshipRole']);

        $worshipRoles = WorshipRole::where('church_id', $this->getCurrentChurch()->id)
            ->orderBy('sort_order')
            ->get();

        // Get ministry members
        $members = $ministry->members()->get();
        if ($ministry->leader && !$members->contains('id', $ministry->leader->id)) {
            $members->prepend($ministry->leader);
        }

        // Get all church songs
        $availableSongs = Song::where('church_id', $this->getCurrentChurch()->id)
            ->orderBy('title')
            ->get();

        // Group team by event_song_id
        $teamBySong = $event->worshipTeam->groupBy('event_song_id');

        return response()->json([
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'date' => $event->date->translatedFormat('l, j F Y'),
                'time' => $event->time?->format('H:i'),
            ],
            'songs' => $event->songs->map(function($s) use ($teamBySong) {
                $songTeam = $teamBySong->get($s->pivot->id, collect());
                return [
                    'id' => $s->id,
                    'event_song_id' => $s->pivot->id,
                    'title' => $s->title,
                    'key' => $s->pivot->key,
                    'url' => route('songs.show', $s),
                    'team' => $songTeam->map(fn($t) => [
                        'id' => $t->id,
                        'person_id' => $t->person_id,
                        'person_name' => $t->person->full_name,
                        'role_id' => $t->worship_role_id,
                        'role_name' => $t->worshipRole->name,
                    ])->values(),
                ];
            }),
            'worshipRoles' => $worshipRoles->map(fn($r) => [
                'id' => $r->id,
                'name' => $r->name,
            ]),
            'members' => $members->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->full_name,
            ]),
            'availableSongs' => $availableSongs->map(fn($s) => [
                'id' => $s->id,
                'title' => $s->title,
                'key' => $s->key,
                'inEvent' => $event->songs->contains('id', $s->id),
            ]),
            'routes' => [
                'addSong' => route('events.songs.add', $event),
                'addTeam' => route('events.worship-team.add', $event),
                'eventUrl' => route('events.show', $event),
            ],
        ]);
    }

    /**
     * Add a song to an event
     */
    public function addSong(Request $request, Event $event)
    {
        $this->authorizeChurch($event);

        $validated = $request->validate([
            'song_id' => 'required|exists:songs,id',
            'key' => 'nullable|string|max:10',
        ]);

        // Get current max order
        $maxOrder = $event->songs()->max('event_songs.order') ?? 0;

        $event->songs()->attach($validated['song_id'], [
            'order' => $maxOrder + 1,
            'key' => $validated['key'] ?? null,
        ]);

        // Get the pivot ID (event_song_id)
        $eventSongId = \DB::table('event_songs')
            ->where('event_id', $event->id)
            ->where('song_id', $validated['song_id'])
            ->value('id');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'event_song_id' => $eventSongId]);
        }

        return back()->with('success', 'Пісню додано');
    }

    /**
     * Remove a song from an event
     */
    public function removeSong(Request $request, Event $event, Song $song)
    {
        $this->authorizeChurch($event);

        // Get the event_song_id before detaching
        $eventSongId = \DB::table('event_songs')
            ->where('event_id', $event->id)
            ->where('song_id', $song->id)
            ->value('id');

        // Remove team members assigned to this song
        if ($eventSongId) {
            EventWorshipTeam::where('event_song_id', $eventSongId)->delete();
        }

        $event->songs()->detach($song->id);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Пісню видалено');
    }

    /**
     * Reorder songs
     */
    public function reorderSongs(Request $request, Event $event)
    {
        $this->authorizeChurch($event);

        $validated = $request->validate([
            'songs' => 'required|array',
            'songs.*' => 'integer|exists:songs,id',
        ]);

        foreach ($validated['songs'] as $order => $songId) {
            $event->songs()->updateExistingPivot($songId, ['order' => $order + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Add a team member to an event
     */
    public function addTeamMember(Request $request, Event $event)
    {
        $this->authorizeChurch($event);

        $validated = $request->validate([
            'person_id' => 'required|exists:people,id',
            'worship_role_id' => 'required|exists:worship_roles,id',
            'event_song_id' => 'nullable|exists:event_songs,id',
            'notes' => 'nullable|string|max:255',
        ]);

        // Check if already exists for this song
        $query = EventWorshipTeam::where('event_id', $event->id)
            ->where('person_id', $validated['person_id'])
            ->where('worship_role_id', $validated['worship_role_id']);

        if (!empty($validated['event_song_id'])) {
            $query->where('event_song_id', $validated['event_song_id']);
        } else {
            $query->whereNull('event_song_id');
        }

        if ($query->exists()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Ця людина вже призначена на цю роль для цієї пісні'], 422);
            }
            return back()->with('error', 'Ця людина вже призначена на цю роль для цієї пісні');
        }

        $member = EventWorshipTeam::create([
            'event_id' => $event->id,
            'event_song_id' => $validated['event_song_id'] ?? null,
            'person_id' => $validated['person_id'],
            'worship_role_id' => $validated['worship_role_id'],
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'id' => $member->id]);
        }

        return back()->with('success', 'Учасника додано');
    }

    /**
     * Remove a team member from an event
     */
    public function removeTeamMember(Request $request, Event $event, EventWorshipTeam $member)
    {
        $this->authorizeChurch($event);

        if ($member->event_id !== $event->id) {
            abort(404);
        }

        $member->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Учасника видалено');
    }

    /**
     * Manage worship roles for the church
     */
    public function roles()
    {
        $roles = WorshipRole::where('church_id', $this->getCurrentChurch()->id)
            ->orderBy('sort_order')
            ->get();

        return view('settings.worship-roles', compact('roles'));
    }

    /**
     * Store a new worship role
     */
    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
        ]);

        $maxOrder = WorshipRole::where('church_id', $this->getCurrentChurch()->id)->max('sort_order') ?? 0;

        WorshipRole::create([
            'church_id' => $this->getCurrentChurch()->id,
            'name' => $validated['name'],
            'icon' => $validated['icon'] ?? null,
            'color' => $validated['color'] ?? null,
            'sort_order' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Роль додано');
    }

    /**
     * Update a worship role
     */
    public function updateRole(Request $request, WorshipRole $role)
    {
        $this->authorizeChurch($role);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
        ]);

        $role->update($validated);

        return back()->with('success', 'Роль оновлено');
    }

    /**
     * Delete a worship role
     */
    public function destroyRole(WorshipRole $role)
    {
        $this->authorizeChurch($role);

        $role->delete();

        return back()->with('success', 'Роль видалено');
    }

    /**
     * Update person's worship skills
     */
    public function updateSkills(Request $request, Person $person)
    {
        $this->authorizeChurch($person);

        $validated = $request->validate([
            'skills' => 'nullable|array',
            'skills.*' => 'exists:worship_roles,id',
            'primary_skill' => 'nullable|exists:worship_roles,id',
        ]);

        // Remove all existing skills
        $person->worshipSkills()->delete();

        // Add new skills
        $skills = $validated['skills'] ?? [];
        $primarySkill = $validated['primary_skill'] ?? null;

        foreach ($skills as $roleId) {
            PersonWorshipSkill::create([
                'person_id' => $person->id,
                'worship_role_id' => $roleId,
                'is_primary' => $roleId == $primarySkill,
            ]);
        }

        return back()->with('success', 'Навички оновлено');
    }

    /**
     * Get members who have a specific skill (for AJAX)
     */
    public function getMembersWithSkill(Request $request, WorshipRole $role)
    {
        $this->authorizeChurch($role);

        $members = Person::where('church_id', $this->getCurrentChurch()->id)
            ->whereHas('worshipSkills', function ($q) use ($role) {
                $q->where('worship_role_id', $role->id);
            })
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);

        return response()->json($members);
    }

    protected function authorizeChurch($model): void
    {
        if ($model->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
