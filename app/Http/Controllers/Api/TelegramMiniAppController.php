<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Event;
use App\Models\EventResponsibility;
use App\Models\Person;
use App\Models\PrayerRequest;
use App\Models\ServicePlanItem;
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
                'notes' => $event->notes,
                'is_service' => $event->is_service,
                'service_type' => $event->service_type_label ?? null,
                'ministry' => $event->ministry ? [
                    'name' => $event->ministry->name,
                    'color' => $event->ministry->color,
                ] : null,
            ]);

        return response()->json(['data' => $events]);
    }

    /**
     * Person's assignments, responsibilities and service plan items
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

        // Service plan items where person is responsible
        $planItems = ServicePlanItem::where('responsible_id', $person->id)
            ->whereHas('event', fn($q) => $q
                ->where('date', '>=', now()->startOfDay())
                ->where('church_id', $person->church_id)
            )
            ->with(['event.ministry:id,name,color', 'song:id,title,artist,key'])
            ->get()
            ->sortBy(fn($item) => $item->event->date)
            ->values()
            ->map(fn(ServicePlanItem $item) => [
                'id' => $item->id,
                'type' => 'plan_item',
                'title' => $item->title,
                'description' => $item->description,
                'type_label' => $item->type_label,
                'type_icon' => $item->type_icon,
                'status' => $item->getPersonStatus($person->id) ?? 'pending',
                'song' => $item->song ? [
                    'title' => $item->song->title,
                    'artist' => $item->song->artist,
                    'key' => $item->song->key,
                ] : null,
                'event' => [
                    'id' => $item->event->id,
                    'title' => $item->event->title,
                    'date' => $item->event->date->format('Y-m-d'),
                    'date_formatted' => $item->event->date->translatedFormat('d M, D'),
                    'time' => $item->event->time?->format('H:i'),
                    'ministry' => $item->event->ministry ? [
                        'name' => $item->event->ministry->name,
                        'color' => $item->event->ministry->color,
                    ] : null,
                ],
            ]);

        return response()->json([
            'assignments' => $assignments,
            'responsibilities' => $responsibilities,
            'plan_items' => $planItems,
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

    public function confirmPlanItem(Request $request, int $id): JsonResponse
    {
        $person = $this->person($request);
        $item = ServicePlanItem::with('event')->find($id);

        if (!$item || $item->responsible_id !== $person->id) {
            return response()->json(['error' => 'Not found'], 404);
        }

        if ($item->event?->church_id !== $person->church_id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $item->setPersonStatus($person->id, 'confirmed');

        return response()->json(['success' => true, 'status' => 'confirmed']);
    }

    public function declinePlanItem(Request $request, int $id): JsonResponse
    {
        $person = $this->person($request);
        $item = ServicePlanItem::with('event')->find($id);

        if (!$item || $item->responsible_id !== $person->id) {
            return response()->json(['error' => 'Not found'], 404);
        }

        if ($item->event?->church_id !== $person->church_id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $item->setPersonStatus($person->id, 'declined');

        return response()->json(['success' => true, 'status' => 'declined']);
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

        $prayer->increment('prayer_count');

        return response()->json([
            'success' => true,
            'prayer_count' => $prayer->fresh()->prayer_count,
        ]);
    }

    /**
     * Upcoming birthdays in the person's church
     */
    public function birthdays(Request $request): JsonResponse
    {
        $person = $this->person($request);

        $birthdays = Person::where('church_id', $person->church_id)
            ->upcomingBirthdays(30)
            ->get()
            ->map(function (Person $p) {
                $nextBirthday = $p->birth_date->copy()->year(now()->year);
                if ($nextBirthday->isPast()) {
                    $nextBirthday->addYear();
                }

                return [
                    'id' => $p->id,
                    'full_name' => $p->full_name,
                    'birth_date' => $nextBirthday->format('Y-m-d'),
                    'date_formatted' => $nextBirthday->translatedFormat('d M, D'),
                    'age' => $p->age,
                    'is_today' => $nextBirthday->isToday(),
                    'days_until' => (int) now()->startOfDay()->diffInDays($nextBirthday->startOfDay(), false),
                ];
            })
            ->sortBy('days_until')
            ->values();

        return response()->json(['data' => $birthdays]);
    }

    /**
     * Person's profile data (enhanced)
     */
    public function profile(Request $request): JsonResponse
    {
        $person = $this->person($request);
        $person->load(['ministries:id,name,color', 'groups:id,name,meeting_day,meeting_time,location']);

        // Upcoming assignments count
        $upcomingAssignments = $person->assignments()
            ->forUpcomingEvents()
            ->count();

        $confirmedAssignments = $person->assignments()
            ->forUpcomingEvents()
            ->confirmed()
            ->count();

        $pendingAssignments = $person->assignments()
            ->forUpcomingEvents()
            ->pending()
            ->count();

        return response()->json([
            'data' => [
                'id' => $person->id,
                'full_name' => $person->full_name,
                'first_name' => $person->first_name,
                'last_name' => $person->last_name,
                'photo_url' => $person->photo_url ?? null,
                'membership_status' => $person->membership_status,
                'membership_label' => $person->membership_status_label ?? null,
                'joined_date' => $person->joined_date?->translatedFormat('d M Y'),
                'ministries' => $person->ministries->map(fn($m) => [
                    'name' => $m->name,
                    'color' => $m->color,
                ]),
                'groups' => $person->groups->map(fn($g) => [
                    'name' => $g->name,
                    'meeting_day' => $g->meeting_day,
                    'meeting_time' => $g->meeting_time?->format('H:i'),
                    'meeting_location' => $g->location,
                    'role' => $g->pivot->role ?? 'member',
                ]),
                'stats' => [
                    'upcoming_assignments' => $upcomingAssignments,
                    'confirmed_assignments' => $confirmedAssignments,
                    'pending_assignments' => $pendingAssignments,
                ],
            ],
        ]);
    }
}
