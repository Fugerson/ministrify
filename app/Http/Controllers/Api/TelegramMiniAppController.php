<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Event;
use App\Models\EventResponsibility;
use App\Models\Person;
use App\Models\PrayerRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramMiniAppController extends Controller
{
    private function person(Request $request): Person
    {
        return $request->attributes->get('tma_person');
    }

    /**
     * Upcoming events for the person's church (next 30 days)
     */
    public function events(Request $request): JsonResponse
    {
        $person = $this->person($request);

        $events = Event::where('church_id', $person->church_id)
            ->where('date', '>=', now()->startOfDay())
            ->where('date', '<=', now()->addDays(30))
            ->with('ministry:id,name,color')
            ->orderBy('date')
            ->orderBy('time')
            ->get()
            ->map(fn(Event $event) => [
                'id' => $event->id,
                'title' => $event->title,
                'date' => $event->date->format('Y-m-d'),
                'date_formatted' => $event->date->translatedFormat('d M, D'),
                'time' => $event->time?->format('H:i'),
                'end_time' => $event->end_time?->format('H:i'),
                'location' => $event->location,
                'ministry' => $event->ministry ? [
                    'name' => $event->ministry->name,
                    'color' => $event->ministry->color,
                ] : null,
            ]);

        return response()->json(['data' => $events]);
    }

    /**
     * Person's assignments and responsibilities for upcoming events
     */
    public function assignments(Request $request): JsonResponse
    {
        $person = $this->person($request);

        // Assignments (position-based)
        $assignments = $person->assignments()
            ->with(['event.ministry:id,name,color', 'position:id,name'])
            ->whereHas('event', fn($q) => $q
                ->where('date', '>=', now()->startOfDay())
                ->where('church_id', $person->church_id)
            )
            ->get()
            ->sortBy(fn($a) => $a->event->date)
            ->values()
            ->map(fn(Assignment $a) => [
                'id' => $a->id,
                'type' => 'assignment',
                'status' => $a->status,
                'status_label' => $a->status_label,
                'status_icon' => $a->status_icon,
                'position' => $a->position?->name,
                'event' => [
                    'id' => $a->event->id,
                    'title' => $a->event->title,
                    'date' => $a->event->date->format('Y-m-d'),
                    'date_formatted' => $a->event->date->translatedFormat('d M, D'),
                    'time' => $a->event->time?->format('H:i'),
                    'ministry' => $a->event->ministry ? [
                        'name' => $a->event->ministry->name,
                        'color' => $a->event->ministry->color,
                    ] : null,
                ],
            ]);

        // Responsibilities (custom tasks)
        $responsibilities = $person->responsibilities()
            ->with(['event.ministry:id,name,color'])
            ->whereHas('event', fn($q) => $q
                ->where('date', '>=', now()->startOfDay())
                ->where('church_id', $person->church_id)
            )
            ->get()
            ->sortBy(fn($r) => $r->event->date)
            ->values()
            ->map(fn(EventResponsibility $r) => [
                'id' => $r->id,
                'type' => 'responsibility',
                'name' => $r->name,
                'status' => $r->status,
                'status_label' => $r->status_label,
                'status_icon' => $r->status_icon,
                'event' => [
                    'id' => $r->event->id,
                    'title' => $r->event->title,
                    'date' => $r->event->date->format('Y-m-d'),
                    'date_formatted' => $r->event->date->translatedFormat('d M, D'),
                    'time' => $r->event->time?->format('H:i'),
                    'ministry' => $r->event->ministry ? [
                        'name' => $r->event->ministry->name,
                        'color' => $r->event->ministry->color,
                    ] : null,
                ],
            ]);

        return response()->json([
            'assignments' => $assignments,
            'responsibilities' => $responsibilities,
        ]);
    }

    public function confirmAssignment(Request $request, int $id): JsonResponse
    {
        $person = $this->person($request);
        $assignment = Assignment::with('event')->find($id);

        if (!$assignment || $assignment->person_id !== $person->id) {
            return response()->json(['error' => 'Not found'], 404);
        }

        if ($assignment->event?->church_id !== $person->church_id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        if (!$assignment->confirm()) {
            return response()->json(['error' => 'Cannot confirm this assignment'], 422);
        }

        return response()->json(['success' => true, 'status' => $assignment->status]);
    }

    public function declineAssignment(Request $request, int $id): JsonResponse
    {
        $person = $this->person($request);
        $assignment = Assignment::with('event')->find($id);

        if (!$assignment || $assignment->person_id !== $person->id) {
            return response()->json(['error' => 'Not found'], 404);
        }

        if ($assignment->event?->church_id !== $person->church_id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        if (!$assignment->decline()) {
            return response()->json(['error' => 'Cannot decline this assignment'], 422);
        }

        return response()->json(['success' => true, 'status' => $assignment->status]);
    }

    public function confirmResponsibility(Request $request, int $id): JsonResponse
    {
        $person = $this->person($request);
        $responsibility = EventResponsibility::with('event')->find($id);

        if (!$responsibility || $responsibility->person_id !== $person->id) {
            return response()->json(['error' => 'Not found'], 404);
        }

        if ($responsibility->event?->church_id !== $person->church_id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $responsibility->confirm();

        return response()->json(['success' => true, 'status' => $responsibility->status]);
    }

    public function declineResponsibility(Request $request, int $id): JsonResponse
    {
        $person = $this->person($request);
        $responsibility = EventResponsibility::with('event')->find($id);

        if (!$responsibility || $responsibility->person_id !== $person->id) {
            return response()->json(['error' => 'Not found'], 404);
        }

        if ($responsibility->event?->church_id !== $person->church_id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $responsibility->decline();

        return response()->json(['success' => true, 'status' => $responsibility->status]);
    }

    /**
     * Published announcements for the person's church
     */
    public function announcements(Request $request): JsonResponse
    {
        $person = $this->person($request);

        $announcements = Announcement::forChurch($person->church_id)
            ->published()
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->limit(30)
            ->get()
            ->map(fn(Announcement $a) => [
                'id' => $a->id,
                'title' => $a->title,
                'content' => $a->content,
                'is_pinned' => $a->is_pinned,
                'published_at' => $a->published_at?->format('Y-m-d'),
                'published_at_formatted' => $a->published_at?->translatedFormat('d M Y'),
            ]);

        return response()->json(['data' => $announcements]);
    }

    /**
     * Public prayer requests for the person's church
     */
    public function prayers(Request $request): JsonResponse
    {
        $person = $this->person($request);

        $prayers = PrayerRequest::where('church_id', $person->church_id)
            ->active()
            ->public()
            ->orderByDesc('is_urgent')
            ->orderByDesc('created_at')
            ->limit(30)
            ->get()
            ->map(fn(PrayerRequest $p) => [
                'id' => $p->id,
                'title' => $p->title,
                'description' => $p->description,
                'is_urgent' => $p->is_urgent,
                'is_anonymous' => $p->is_anonymous,
                'author_name' => $p->author_name,
                'prayer_count' => $p->prayer_count ?? 0,
                'created_at' => $p->created_at->translatedFormat('d M Y'),
            ]);

        return response()->json(['data' => $prayers]);
    }

    /**
     * Increment prayer count for a request
     */
    public function prayForRequest(Request $request, int $id): JsonResponse
    {
        $person = $this->person($request);
        $prayer = PrayerRequest::where('church_id', $person->church_id)
            ->active()
            ->public()
            ->find($id);

        if (!$prayer) {
            return response()->json(['error' => 'Not found'], 404);
        }

        // Simple increment — no user tracking needed for TMA (no User model)
        $prayer->increment('prayer_count');

        return response()->json([
            'success' => true,
            'prayer_count' => $prayer->fresh()->prayer_count,
        ]);
    }

    /**
     * Person's profile data
     */
    public function profile(Request $request): JsonResponse
    {
        $person = $this->person($request);
        $person->load(['ministries:id,name,color', 'groups:id,name']);

        // Upcoming assignments count
        $upcomingAssignments = $person->assignments()
            ->forUpcomingEvents()
            ->count();

        $confirmedAssignments = $person->assignments()
            ->forUpcomingEvents()
            ->confirmed()
            ->count();

        return response()->json([
            'data' => [
                'id' => $person->id,
                'full_name' => $person->full_name,
                'first_name' => $person->first_name,
                'last_name' => $person->last_name,
                'photo_url' => $person->photo_url ?? null,
                'ministries' => $person->ministries->map(fn($m) => [
                    'name' => $m->name,
                    'color' => $m->color,
                ]),
                'groups' => $person->groups->map(fn($g) => [
                    'name' => $g->name,
                ]),
                'stats' => [
                    'upcoming_assignments' => $upcomingAssignments,
                    'confirmed_assignments' => $confirmedAssignments,
                ],
            ],
        ]);
    }
}
