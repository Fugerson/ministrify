<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventMinistryTeam;
use App\Models\EventWorshipTeam;
use App\Models\Ministry;
use App\Models\MinistryRole;
use App\Models\Person;
use App\Models\PersonWorshipSkill;
use App\Models\Song;
use App\Models\WorshipRole;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorshipTeamController extends Controller
{
    /**
     * Get worship events for a ministry portal
     */
    public function events(Ministry $ministry)
    {
        $this->authorizeChurch($ministry);

        if (!$ministry->is_worship_ministry && !$ministry->is_sunday_service_part) {
            abort(404);
        }

        $events = Event::where('church_id', $this->getCurrentChurch()->id)
            ->where('service_type', 'sunday_service')
            ->where('date', '>=', now()->subDays(7))
            ->with(['songs', 'ministryTeams' => fn($q) => $q->where('ministry_id', $ministry->id)->with('person', 'ministryRole')])
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        $ministryRoles = $ministry->ministryRoles()->orderBy('sort_order')->get();

        return view('ministries.worship-events', compact('ministry', 'events', 'ministryRoles'));
    }

    /**
     * Show worship statistics
     */
    public function stats(Request $request, Ministry $ministry)
    {
        $this->authorizeChurch($ministry);

        if (!$ministry->is_worship_ministry && !$ministry->is_sunday_service_part) {
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
            ->where('service_type', 'sunday_service');

        if ($startDate) {
            $eventsQuery->where('date', '>=', $startDate);
        }

        $eventIds = $eventsQuery->pluck('id');

        // Summary stats
        $stats = [
            'total_events' => $eventIds->count(),
            'total_participants' => EventMinistryTeam::whereIn('event_id', $eventIds)
                ->where('ministry_id', $ministry->id)
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
        $topParticipants = EventMinistryTeam::whereIn('event_id', $eventIds)
            ->where('ministry_id', $ministry->id)
            ->select('person_id', \DB::raw('COUNT(*) as count'))
            ->groupBy('person_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Load person and roles for each participant
        foreach ($topParticipants as $participant) {
            $participant->person = Person::find($participant->person_id);
            $participant->roles = MinistryRole::whereIn('id', function($q) use ($participant, $eventIds, $ministry) {
                $q->select('ministry_role_id')
                    ->from('event_ministry_team')
                    ->where('person_id', $participant->person_id)
                    ->where('ministry_id', $ministry->id)
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
        $roleStats = MinistryRole::where('ministry_id', $ministry->id)
            ->withCount(['eventTeamMembers as count' => function($q) use ($eventIds) {
                $q->whereIn('event_id', $eventIds);
            }])
            ->orderByDesc('count')
            ->get();

        // Recent events
        $recentEvents = Event::where('church_id', $churchId)
            ->where('service_type', 'sunday_service')
            ->where('date', '<=', now())
            ->withCount(['songs as songs_count', 'ministryTeams as team_count' => function($q) use ($ministry) {
                $q->where('ministry_id', $ministry->id);
            }])
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
        // Redirect to worship ministry's schedule tab — detail is now a modal
        $worshipMinistry = Ministry::where('church_id', $ministry->church_id)
            ->where('is_worship_ministry', true)
            ->first();

        $target = $worshipMinistry ?? $ministry;

        return redirect()->route('ministries.show', ['ministry' => $target, 'tab' => 'schedule']);
    }

    /**
     * Get event data as JSON for modal
     */
    public function eventData(Ministry $ministry, Event $event)
    {
        $this->authorizeChurch($ministry);
        $this->authorizeChurch($event);

        if (!$ministry->is_worship_ministry && !$ministry->is_sunday_service_part) {
            abort(404);
        }

        $event->load(['songs', 'ministryTeams' => fn($q) => $q->where('ministry_id', $ministry->id)->with('person', 'ministryRole')]);

        $ministryRoles = $ministry->ministryRoles()->orderBy('sort_order')->get();

        // Get ministry members
        $members = $ministry->members()->get();
        if ($ministry->leader && !$members->contains('id', $ministry->leader->id)) {
            $members->prepend($ministry->leader);
        }

        // Get all church songs (only for worship ministries)
        $availableSongs = collect();
        if ($ministry->is_worship_ministry) {
            $availableSongs = Song::where('church_id', $this->getCurrentChurch()->id)
                ->orderBy('title')
                ->get();
        }

        // Group team by event_song_id
        $teamBySong = $event->ministryTeams->groupBy('event_song_id');

        // General team (no song assigned)
        $generalTeam = $teamBySong->get('', collect())->merge($teamBySong->get(null, collect()));

        return response()->json([
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'date' => $event->date->translatedFormat('l, j F Y'),
                'time' => $event->time?->format('H:i'),
            ],
            'songs' => $ministry->is_worship_ministry ? $event->songs->map(function($s) use ($teamBySong) {
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
                        'role_id' => $t->ministry_role_id,
                        'role_name' => $t->ministryRole?->name ?? '',
                    ])->values(),
                ];
            }) : [],
            'generalTeam' => $generalTeam->map(fn($t) => [
                'id' => $t->id,
                'person_id' => $t->person_id,
                'person_name' => $t->person->full_name,
                'role_id' => $t->ministry_role_id,
                'role_name' => $t->ministryRole?->name ?? '',
            ])->values(),
            'ministryRoles' => $ministryRoles->map(fn($r) => [
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
            'isWorshipMinistry' => $ministry->is_worship_ministry,
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
            'song_id' => ['required', Rule::exists('songs', 'id')->where('church_id', $this->getCurrentChurch()->id)],
            'key' => 'nullable|string|max:10',
        ]);

        // Get current max order
        $maxOrder = $event->songs()->max('event_songs.order') ?? 0;

        $event->songs()->attach($validated['song_id'], [
            'order' => $maxOrder + 1,
            'key' => $validated['key'] ?? null,
        ]);

        // Get the pivot ID of the just-created record (latest by order)
        $eventSongId = \DB::table('event_songs')
            ->where('event_id', $event->id)
            ->where('song_id', $validated['song_id'])
            ->orderByDesc('id')
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

        // Remove team members assigned to this song (from both tables for backwards compat)
        if ($eventSongId) {
            EventMinistryTeam::where('event_song_id', $eventSongId)->delete();
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
     * Add a team member to an event (using MinistryRole + EventMinistryTeam)
     */
    public function addTeamMember(Request $request, Event $event)
    {
        $this->authorizeChurch($event);

        $validated = $request->validate([
            'person_id' => ['required', Rule::exists('people', 'id')->where('church_id', $this->getCurrentChurch()->id)],
            'ministry_role_id' => ['required', 'exists:ministry_roles,id'],
            'ministry_id' => ['required', 'exists:ministries,id'],
            'event_song_id' => 'nullable|exists:event_songs,id',
            'notes' => 'nullable|string|max:255',
        ]);

        // Verify ministry belongs to same church
        $ministry = Ministry::find($validated['ministry_id']);
        if (!$ministry || $ministry->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }

        // Verify role belongs to the ministry
        $role = MinistryRole::find($validated['ministry_role_id']);
        if (!$role || $role->ministry_id !== $ministry->id) {
            abort(404);
        }

        // Check if already exists for this song
        $query = EventMinistryTeam::where('event_id', $event->id)
            ->where('ministry_id', $validated['ministry_id'])
            ->where('person_id', $validated['person_id'])
            ->where('ministry_role_id', $validated['ministry_role_id']);

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

        $member = EventMinistryTeam::create([
            'event_id' => $event->id,
            'ministry_id' => $validated['ministry_id'],
            'event_song_id' => $validated['event_song_id'] ?? null,
            'person_id' => $validated['person_id'],
            'ministry_role_id' => $validated['ministry_role_id'],
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
    public function removeTeamMember(Request $request, Event $event, EventMinistryTeam $member)
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
     * Update person's worship skills
     */
    public function updateSkills(Request $request, Person $person)
    {
        $this->authorizeChurch($person);

        $validated = $request->validate([
            'skills' => 'nullable|array',
            'skills.*' => [Rule::exists('worship_roles', 'id')->where('church_id', $this->getCurrentChurch()->id)],
            'primary_skill' => ['nullable', Rule::exists('worship_roles', 'id')->where('church_id', $this->getCurrentChurch()->id)],
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
