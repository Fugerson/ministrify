<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\AuditLog;
use App\Models\Board;
use App\Models\Event;
use App\Models\EventMinistryTeam;
use App\Models\Ministry;
use App\Models\MinistryMeeting;
use App\Rules\BelongsToChurch;
use App\Services\CalendarService;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->canView('events')) {
            return $this->errorResponse($request, __('У вас немає доступу до цього розділу. Зверніться до адміністратора церкви для отримання потрібних прав.'));
        }

        $church = $this->getCurrentChurch();

        $query = Event::where('church_id', $church->id)
            ->with(['ministry.positions', 'responsibilities.person', 'assignments']);

        if ($ministryId = $request->get('ministry')) {
            $query->where('ministry_id', $ministryId);
        }

        $showPast = $request->boolean('past');

        if ($showPast) {
            $query->where('date', '<', now()->startOfDay())->orderByDesc('date')->orderByDesc('time');
        } else {
            $query->upcoming();
        }

        $events = $query->paginate(20);
        $ministries = $church->ministries;

        return view('schedule.index', compact('events', 'ministries', 'showPast'));
    }

    public function schedule(Request $request)
    {
        if (!auth()->user()->canView('events')) {
            return $this->errorResponse($request, __('У вас немає доступу до цього розділу. Зверніться до адміністратора церкви для отримання потрібних прав.'));
        }

        $church = $this->getCurrentChurch();

        $view = $request->get('view', 'month');
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $week = $request->get('week');

        $gridStart = null;
        $gridEnd = null;

        if ($view === 'week') {
            if ($week) {
                $startDate = Carbon::create($year)->setISODate($year, $week)->startOfWeek(Carbon::MONDAY);
            } else {
                $startDate = now()->startOfWeek(Carbon::MONDAY);
            }
            $endDate = $startDate->copy()->endOfWeek(Carbon::SUNDAY);
            $currentWeek = $startDate->weekOfYear;
        } else {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $currentWeek = null;

            // Extend date range to cover the full visible calendar grid
            // (includes days from prev/next month visible in the grid)
            $gridStart = $startDate->copy()->startOfWeek(Carbon::MONDAY);
            $gridEnd = $endDate->copy()->endOfWeek(Carbon::SUNDAY);
        }

        // For month view, use full grid range (includes neighboring month days visible in calendar)
        $queryStart = $gridStart ?? $startDate;
        $queryEnd = $gridEnd ?? $endDate;

        // Include events that span into this date range (multi-day events)
        $events = Event::where('church_id', $church->id)
            ->where(function ($q) use ($queryStart, $queryEnd) {
                // Events starting in range
                $q->whereBetween('date', [$queryStart, $queryEnd])
                    // OR events that started before but end in/after range
                    ->orWhere(function ($q2) use ($queryStart, $queryEnd) {
                        $q2->where('date', '<', $queryStart)
                           ->whereNotNull('end_date')
                           ->where('end_date', '>=', $queryStart);
                    });
            })
            ->with(['ministry', 'responsibilities.person'])
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        // Get ministry meetings for the same period
        $meetings = MinistryMeeting::whereHas('ministry', fn($q) => $q->where('church_id', $church->id))
            ->whereBetween('date', [$queryStart, $queryEnd])
            ->with('ministry')
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        // Combine events and meetings into unified collection
        $calendarItems = collect();

        foreach ($events as $event) {
            // For multi-day events, create an entry for each day
            $eventStart = Carbon::parse($event->date);
            $eventEnd = $event->end_date ? Carbon::parse($event->end_date) : $eventStart;

            // Clip to view range
            $displayStart = $eventStart->max($queryStart);
            $displayEnd = $eventEnd->min($queryEnd);

            $currentDate = $displayStart->copy();
            while ($currentDate <= $displayEnd) {
                $isFirstDay = $currentDate->isSameDay($eventStart);
                $isLastDay = $currentDate->isSameDay($eventEnd);
                $isMultiDay = !$eventStart->isSameDay($eventEnd);

                $calendarItems->push((object)[
                    'type' => 'event',
                    'id' => $event->id,
                    'title' => $event->title,
                    'date' => $currentDate->copy(),
                    'time' => $isFirstDay ? $event->time : null,
                    'ministry' => $event->ministry,
                    'ministry_id' => $event->ministry_id,
                    'ministry_display_name' => $event->ministry_display_name,
                    'ministry_display_color' => $event->ministry_display_color,
                    'original' => $event,
                    'is_multi_day' => $isMultiDay,
                    'is_first_day' => $isFirstDay,
                    'is_last_day' => $isLastDay,
                ]);
                $currentDate->addDay();
            }
        }

        foreach ($meetings as $meeting) {
            $calendarItems->push((object)[
                'type' => 'meeting',
                'id' => $meeting->id,
                'title' => $meeting->title,
                'date' => $meeting->date,
                'time' => $meeting->start_time,
                'ministry' => $meeting->ministry,
                'ministry_id' => $meeting->ministry_id,
                'original' => $meeting,
            ]);
        }

        // Group by date
        $events = $calendarItems->groupBy(fn($item) => Carbon::parse($item->date)->format('Y-m-d'));

        // Get upcoming events from next month (first 7 days) for month view
        $upcomingNextMonth = collect();
        if ($view === 'month') {
            $nextMonthStart = $endDate->copy()->addDay()->startOfDay();
            $nextMonthEnd = $nextMonthStart->copy()->addDays(6)->endOfDay();

            $upcomingEvents = Event::where('church_id', $church->id)
                ->whereBetween('date', [$nextMonthStart, $nextMonthEnd])
                ->with(['ministry', 'responsibilities.person'])
                ->orderBy('date')
                ->orderBy('time')
                ->get();

            foreach ($upcomingEvents as $event) {
                $upcomingNextMonth->push((object)[
                    'type' => 'event',
                    'id' => $event->id,
                    'title' => $event->title,
                    'date' => $event->date,
                    'time' => $event->time,
                    'ministry' => $event->ministry,
                    'ministry_id' => $event->ministry_id,
                    'ministry_display_name' => $event->ministry_display_name,
                    'ministry_display_color' => $event->ministry_display_color,
                    'original' => $event,
                ]);
            }
        }

        $ministries = $church->ministries;

        // Calculate next month for display
        $nextMonth = $month == 12 ? 1 : $month + 1;
        $nextYear = $month == 12 ? $year + 1 : $year;

        // Google Calendar OAuth status
        $googleSettings = auth()->user()->settings['google_calendar'] ?? null;
        $isGoogleConnected = !empty($googleSettings['access_token']);
        $lastSyncedAt = $googleSettings['last_synced_at'] ?? null;

        // Service types for matrix tab
        $serviceTypes = Event::serviceTypeLabels();

        return view('schedule.calendar', compact(
            'events', 'year', 'month', 'startDate', 'endDate',
            'ministries', 'view', 'currentWeek', 'church', 'meetings', 'upcomingNextMonth', 'nextMonth', 'nextYear',
            'isGoogleConnected', 'lastSyncedAt', 'serviceTypes'
        ));
    }

    public function calendar(Request $request)
    {
        return $this->schedule($request);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Event::class);

        $church = $this->getCurrentChurch();
        $user = auth()->user();

        $query = Ministry::where('church_id', $church->id);

        // Non-admins without global edit permission see only their own ministries
        if (!$user->canEdit('events') && $user->person) {
            $query->where('leader_id', $user->person->id);
        }

        $ministries = $query->with('positions')->get();

        $selectedMinistry = $request->get('ministry_id') ?? $request->get('ministry');

        return view('schedule.create', compact('ministries', 'selectedMinistry'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Event::class);

        $validated = $request->validate([
            'ministry_id' => ['nullable', 'exists:ministries,id', new BelongsToChurch(Ministry::class)],
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'nullable|date_format:H:i',
            'end_date' => 'nullable|date|after_or_equal:date',
            'notes' => 'nullable|string|max:5000',
            'recurrence_rule' => 'nullable|array',
            'recurrence_rule.frequency' => 'required_with:recurrence_rule|string|in:daily,weekly,biweekly,monthly',
            'recurrence_rule.days' => 'nullable|array',
            'recurrence_rule.day_of_month' => 'nullable|integer',
            'recurrence_rule.interval' => 'nullable|integer|min:1',
            'recurrence_end_type' => 'nullable|string|in:count,date',
            'recurrence_end_count' => 'nullable|integer|min:2|max:365',
            'recurrence_end_date' => 'nullable|date',
            'is_service' => 'nullable|boolean',
            'service_type' => 'nullable|string|in:sunday_service,youth_meeting,prayer_meeting,special_service',
            'track_attendance' => 'nullable|boolean',
            'reminders' => 'nullable|array',
            'reminders.*.type' => 'required_with:reminders|in:days,hours',
            'reminders.*.value' => 'required_with:reminders|integer|min:1|max:30',
            'reminders.*.time' => 'nullable|date_format:H:i',
            'reminders.*.recipients' => 'nullable|in:all,confirmed,pending,custom',
            'reminders.*.person_ids' => 'nullable|array',
        ]);

        // Handle all-day events - clear time if all_day is true
        if ($request->boolean('all_day')) {
            $validated['time'] = null;
            $validated['end_time'] = null;
        }

        $validated['is_service'] = $request->boolean('is_service');
        $validated['track_attendance'] = $request->boolean('track_attendance');

        // Process reminder settings
        if (!empty($validated['reminders'])) {
            $validated['reminder_settings'] = array_values(array_map(function ($reminder) {
                return [
                    'type' => $reminder['type'],
                    'value' => (int) $reminder['value'],
                    'time' => $reminder['time'] ?? null,
                    'recipients' => $reminder['recipients'] ?? 'all',
                    'person_ids' => isset($reminder['person_ids']) ? array_map('intval', $reminder['person_ids']) : [],
                ];
            }, $validated['reminders']));
        }
        unset($validated['reminders']);

        $church = $this->getCurrentChurch();

        // If ministry selected, authorize
        if (!empty($validated['ministry_id'])) {
            $ministry = Ministry::where('church_id', $church->id)->findOrFail($validated['ministry_id']);
            Gate::authorize('contribute-ministry', $ministry);
        }

        $validated['church_id'] = $church->id;
        $validated['created_by'] = auth()->id();

        // Set Google Calendar ID if provided
        $googleCalendarId = $request->input('google_calendar_id');
        if ($googleCalendarId) {
            $validated['google_calendar_id'] = $googleCalendarId;
        }

        $event = Event::create($validated);

        // Handle recurring events
        if (!empty($validated['recurrence_rule'])) {
            $this->generateRecurringEvents(
                $event,
                $validated['recurrence_rule'],
                $validated['recurrence_end_type'] ?? 'count',
                $validated['recurrence_end_count'] ?? 12,
                $validated['recurrence_end_date'] ?? null
            );
        }

        return $this->successResponse($request, 'Подію створено.', 'events.show', ['event' => $event]);
    }

    public function show(Event $event)
    {
        $this->authorizeChurch($event);
        $this->authorize('view', $event);
        $church = $this->getCurrentChurch();

        $event->load([
            'ministry',
            'songs',
            'ministryTeams.person',
            'ministryTeams.ministryRole',
            'ministryTeams.ministry',
            'checklist.items.completedByUser',
            'planItems.responsible',
            'planItems.song',
            'responsibilities.person',
            'attendance.records.person',
            'assignments.person',
            'assignments.position.ministry',
        ]);

        // Get available people for unfilled positions (only if ministry exists)
        $availablePeople = collect();
        $volunteerBlockouts = [];
        if ($event->ministry) {
            $availablePeople = $event->ministry->members()
                ->with(['blockoutDates' => function ($q) use ($event) {
                    $q->active()->forDate($event->date);
                }])
                ->get();

            // Build blockout info using already loaded blockoutDates (no extra queries)
            foreach ($availablePeople as $person) {
                // Filter loaded blockouts for this ministry or all ministries
                $blockout = $person->blockoutDates->first(function ($b) use ($event) {
                    return $b->ministry_id === null || $b->ministry_id === $event->ministry_id;
                });

                if ($blockout) {
                    $volunteerBlockouts[$person->id] = $blockout->reason_label;
                }
            }
        }

        // Get checklist templates
        $checklistTemplates = \App\Models\ChecklistTemplate::where('church_id', $church->id)
            ->with('items')
            ->get();

        // Get boards for task creation
        $boards = Board::where('church_id', $church->id)
            ->where('is_archived', false)
            ->get();

        // Get all church members for attendance tracking and plan item assignment
        $allPeople = \App\Models\Person::where('church_id', $church->id)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        // Get ministries for inline editing (same filter as create)
        $ministryQuery = Ministry::where('church_id', $church->id);
        $user = auth()->user();
        if (!$user->canEdit('events') && $user->person) {
            $ministryQuery->where('leader_id', $user->person->id);
        }
        $ministries = $ministryQuery->get();

        // Get songs for autocomplete in service plan (only songs assigned to this event by worship team)
        $songsForAutocomplete = $event->songs->map(function ($song) use ($event) {
            $eventSongId = $song->pivot->id;
            $songTeam = $event->ministryTeams->where('event_song_id', $eventSongId);
            $eventLevelTeam = $event->ministryTeams->whereNull('event_song_id');
            $allTeam = $songTeam->merge($eventLevelTeam)->unique('id');

            return [
                'id' => $song->id,
                'title' => $song->title,
                'artist' => $song->artist,
                'key' => $song->pivot->key ?? $song->key,
                'team' => $allTeam->map(fn ($t) => [
                    'person_name' => $t->person?->full_name,
                    'role_name' => $t->ministryRole?->name,
                ])->values()->toArray(),
            ];
        });

        $canEdit = auth()->user()->can('update', $event);

        return view('schedule.show', compact('event', 'availablePeople', 'volunteerBlockouts', 'checklistTemplates', 'boards', 'allPeople', 'ministries', 'songsForAutocomplete', 'canEdit'));
    }

    public function edit(Event $event)
    {
        $this->authorizeChurch($event);
        $this->authorize('update', $event);

        $church = $this->getCurrentChurch();
        $user = auth()->user();

        $ministryQuery = Ministry::where('church_id', $church->id);
        if (!$user->canEdit('events') && $user->person) {
            $ministryQuery->where('leader_id', $user->person->id);
        }
        $ministries = $ministryQuery->get();

        return view('schedule.edit', compact('event', 'ministries'));
    }

    public function update(Request $request, Event $event)
    {
        $this->authorizeChurch($event);
        $this->authorize('update', $event);

        // Support partial updates for AJAX
        $rules = [
            'ministry_id' => ['nullable', 'exists:ministries,id', new BelongsToChurch(Ministry::class)],
            'title' => 'sometimes|string|max:255',
            'date' => 'sometimes|date',
            'time' => 'sometimes|date_format:H:i',
            'end_date' => 'nullable|date|after_or_equal:date',
            'notes' => 'nullable|string|max:5000',
            'is_service' => 'nullable|boolean',
            'service_type' => 'nullable|string|in:sunday_service,youth_meeting,prayer_meeting,special_service',
            'track_attendance' => 'nullable|boolean',
            'reminders' => 'nullable|array',
            'reminders.*.type' => 'required_with:reminders|in:days,hours',
            'reminders.*.value' => 'required_with:reminders|integer|min:1|max:30',
            'reminders.*.time' => 'nullable|date_format:H:i',
            'reminders.*.recipients' => 'nullable|in:all,confirmed,pending,custom',
            'reminders.*.person_ids' => 'nullable|array',
        ];

        $validated = $request->validate($rules);

        // Handle all-day events
        if ($request->has('all_day')) {
            if ($request->boolean('all_day')) {
                $validated['time'] = null;
                $validated['end_time'] = null;
            }
        }

        if ($request->has('is_service')) {
            $validated['is_service'] = $request->boolean('is_service');
        }
        if ($request->has('track_attendance')) {
            $validated['track_attendance'] = $request->boolean('track_attendance');
        }

        // Handle Google Calendar binding
        if ($request->has('google_calendar_id')) {
            $googleCalendarId = $request->input('google_calendar_id');
            if ($googleCalendarId) {
                $validated['google_calendar_id'] = $googleCalendarId;
            } else {
                // User chose "don't sync" — if event was synced, delete from Google first
                if ($event->google_event_id && $event->google_calendar_id) {
                    try {
                        $gcService = app(\App\Services\GoogleCalendarService::class);
                        $user = \App\Models\User::whereHas('churches', fn ($q) => $q->where('churches.id', $event->church_id))
                            ->whereNotNull('settings->google_calendar->access_token')
                            ->first();
                        if ($user) {
                            $accessToken = $gcService->getValidToken($user);
                            if ($accessToken) {
                                $gcService->deleteEvent($accessToken, $event->google_calendar_id, $event->google_event_id);
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Failed to delete event from Google Calendar on unsync', [
                            'event_id' => $event->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                $validated['google_calendar_id'] = null;
                $validated['google_event_id'] = null;
                $validated['google_synced_at'] = null;
                $validated['google_sync_status'] = null;
            }
        }

        // Process reminder settings
        if ($request->has('reminders')) {
            if (!empty($validated['reminders'])) {
                $validated['reminder_settings'] = array_values(array_map(function ($reminder) {
                    return [
                        'type' => $reminder['type'],
                        'value' => (int) $reminder['value'],
                        'time' => $reminder['time'] ?? null,
                        'recipients' => $reminder['recipients'] ?? 'all',
                        'person_ids' => isset($reminder['person_ids']) ? array_map('intval', $reminder['person_ids']) : [],
                    ];
                }, $validated['reminders']));
            } else {
                $validated['reminder_settings'] = null;
            }
            unset($validated['reminders']);
        }

        $event->update($validated);

        return $this->successResponse($request, 'Подію оновлено.', 'events.show', ['event' => $event], [
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'date' => $event->date?->format('Y-m-d'),
                'time' => $event->time?->format('H:i'),
                'notes' => $event->notes,
                'ministry_id' => $event->ministry_id,
                'ministry_name' => $event->ministry?->name,
                'ministry_color' => $event->ministry?->color,
                'is_service' => $event->is_service,
                'track_attendance' => $event->track_attendance,
            ],
        ]);
    }

    public function destroy(Request $request, Event $event)
    {
        $this->authorizeChurch($event);
        $this->authorize('delete', $event);

        $googleCalendar = app(GoogleCalendarService::class);
        $user = auth()->user();

        $deleteSeries = $request->boolean('delete_series');

        if ($deleteSeries) {
            // Delete all events in the series (scoped to church)
            $parentId = $event->parent_event_id ?? $event->id;

            $seriesEvents = Event::where('church_id', $event->church_id)
                ->where(fn($q) => $q->where('parent_event_id', $parentId)->orWhere('id', $parentId))
                ->get();

            foreach ($seriesEvents as $seriesEvent) {
                if ($seriesEvent->google_event_id) {
                    $googleCalendar->deleteAndUnlink($user, $seriesEvent);
                }
                $seriesEvent->delete();
            }

            return $this->successResponse($request, 'Серію подій видалено.', 'schedule');
        }

        if ($event->google_event_id) {
            $googleCalendar->deleteAndUnlink($user, $event);
        }

        $event->delete();

        return $this->successResponse($request, 'Подію видалено.', 'schedule');
    }

    public function saveAttendance(Request $request, Event $event)
    {
        $this->authorizeChurch($event);

        if (!auth()->user()->canEdit('attendance') && !auth()->user()->canEdit('events')) {
            abort(403);
        }

        if (!$event->track_attendance) {
            abort(404);
        }

        $validated = $request->validate([
            'present' => 'nullable|array',
            'present.*' => ['integer', new BelongsToChurch(\App\Models\Person::class)],
            'guests_count' => 'nullable|integer|min:0',
        ]);

        $church = $this->getCurrentChurch();
        $presentIds = $validated['present'] ?? [];

        \Illuminate\Support\Facades\DB::transaction(function () use ($event, $church, $validated, $presentIds) {
            // Get or create attendance record with lock
            $attendance = Attendance::lockForUpdate()->firstOrCreate(
                [
                    'attendable_type' => Event::class,
                    'attendable_id' => $event->id,
                ],
                [
                    'church_id' => $church->id,
                    'type' => Attendance::TYPE_EVENT,
                    'date' => $event->date,
                    'time' => $event->time,
                    'recorded_by' => auth()->id(),
                ]
            );

            // Update guests count
            $attendance->update([
                'guests_count' => $validated['guests_count'] ?? 0,
            ]);

            // Delete existing records and create new ones
            $attendance->records()->delete();

            foreach ($presentIds as $personId) {
                AttendanceRecord::create([
                    'attendance_id' => $attendance->id,
                    'person_id' => $personId,
                    'present' => true,
                    'checked_in_at' => now()->format('H:i'),
                ]);
            }

            // Recalculate counts
            $attendance->recalculateCounts();
        });

        // Log attendance saved
        $this->logAuditAction('attendance_saved', 'Event', $event->id, $event->title, [
            'present_count' => count($presentIds),
            'guests_count' => $validated['guests_count'] ?? 0,
            'date' => $event->date?->format('Y-m-d'),
        ]);

        return response()->json(['success' => true]);
    }

    public function mySchedule(Request $request)
    {
        $user = auth()->user();

        if (!$user->person) {
            return $this->errorResponse($request, 'Ваш профіль не знайдено.');
        }

        $responsibilities = $user->person->responsibilities()
            ->with(['event.ministry'])
            ->whereHas('event', fn($q) => $q->where('date', '>=', now()->startOfDay()))
            ->get()
            ->sortBy(fn($r) => $r->event->date);

        $assignments = $user->person->assignments()
            ->with(['event.ministry', 'position'])
            ->whereHas('event', fn($q) => $q->where('date', '>=', now()->startOfDay()))
            ->get()
            ->sortBy(fn($a) => $a->event->date);

        return view('schedule.my-schedule', compact('responsibilities', 'assignments'));
    }

    private function generateRecurringEvents(
        Event $parentEvent,
        array $rule,
        string $endType = 'count',
        int $endCount = 12,
        ?string $endDate = null
    ): void {
        $dates = $this->calculateRecurringDates($parentEvent->date, $rule, $endType, $endCount, $endDate);

        // Calculate duration in days for multi-day events
        $durationDays = null;
        if ($parentEvent->end_date) {
            $durationDays = Carbon::parse($parentEvent->date)->diffInDays(Carbon::parse($parentEvent->end_date));
        }

        foreach ($dates as $date) {
            $eventData = [
                'church_id' => $parentEvent->church_id,
                'ministry_id' => $parentEvent->ministry_id,
                'title' => $parentEvent->title,
                'date' => $date,
                'time' => $parentEvent->time,
                'end_time' => $parentEvent->end_time,
                'notes' => $parentEvent->notes,
                'is_service' => $parentEvent->is_service,
                'service_type' => $parentEvent->service_type,
                'track_attendance' => $parentEvent->track_attendance,
                'reminder_settings' => $parentEvent->reminder_settings,
                'google_calendar_id' => $parentEvent->google_calendar_id,
                'parent_event_id' => $parentEvent->id,
            ];

            if ($durationDays !== null) {
                $eventData['end_date'] = $date->copy()->addDays($durationDays);
            }

            Event::create($eventData);
        }
    }

    private function calculateRecurringDates(
        Carbon $startDate,
        array $rule,
        string $endType,
        int $endCount,
        ?string $endDate
    ): array {
        $dates = [];
        $currentDate = $startDate->copy();
        $maxDate = $endType === 'date' && $endDate ? Carbon::parse($endDate) : null;
        $count = 0;
        $maxIterations = $endType === 'count' ? $endCount - 1 : 365; // -1 because first event already created

        $frequency = $rule['frequency'] ?? 'weekly';
        $interval = $rule['interval'] ?? 1;

        while ($count < $maxIterations) {
            // Calculate next date based on frequency
            switch ($frequency) {
                case 'daily':
                    $currentDate->addDays($interval);
                    break;
                case 'weekly':
                    $currentDate->addWeeks($interval);
                    break;
                case 'biweekly':
                    $currentDate->addWeeks(2);
                    break;
                case 'monthly':
                    $currentDate->addMonths($interval);
                    break;
                case 'yearly':
                    $currentDate->addYears($interval);
                    break;
                case 'weekdays':
                    do {
                        $currentDate->addDay();
                    } while ($currentDate->isWeekend());
                    break;
                default:
                    return $dates;
            }

            // Check if we've passed the end date
            if ($maxDate && $currentDate->gt($maxDate)) {
                break;
            }

            $dates[] = $currentDate->copy();
            $count++;
        }

        return $dates;
    }

    /**
     * Export calendar to iCal format
     */
    public function exportIcal(Request $request, CalendarService $calendarService)
    {
        abort_unless(auth()->user()->canView('events'), 403);

        $church = $this->getCurrentChurch();

        $ministryId = $request->get('ministry');
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : null;
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : null;

        $icalContent = $calendarService->exportToIcal($church, $ministryId, $startDate, $endDate);

        $filename = 'calendar-' . $church->slug . '-' . now()->format('Y-m-d') . '.ics';

        return response($icalContent)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Show import form
     */
    public function importForm()
    {
        if (!auth()->user()->canCreate('events')) {
            abort(403);
        }
        $church = $this->getCurrentChurch();
        $ministries = Ministry::where('church_id', $church->id)->get();

        return view('schedule.import', compact('ministries'));
    }

    /**
     * Import events from iCal file
     */
    public function importIcal(Request $request, CalendarService $calendarService)
    {
        $request->validate([
            'file' => 'required|file|mimes:ics,txt|max:5120',
            'ministry_id' => 'required|exists:ministries,id',
        ]);

        $church = $this->getCurrentChurch();
        $ministry = Ministry::where('church_id', $church->id)->findOrFail($request->ministry_id);

        Gate::authorize('contribute-ministry', $ministry);

        $result = $calendarService->importFromIcal($request->file('file'), $church, $ministry);

        // Log import
        $this->logAuditAction('imported', 'Event', null, 'Імпорт з iCal файлу', [
            'ministry_id' => $ministry->id,
            'ministry_name' => $ministry->name,
            'total_imported' => $result['total_imported'],
            'total_skipped' => $result['total_skipped'],
            'total_errors' => $result['total_errors'],
        ]);

        $message = "Імпортовано подій: {$result['total_imported']}";
        if ($result['total_skipped'] > 0) {
            $message .= ", пропущено: {$result['total_skipped']}";
        }
        if ($result['total_errors'] > 0) {
            $message .= ", помилок: {$result['total_errors']}";
        }

        return $this->successResponse($request, $message, 'schedule');
    }

    /**
     * Add single event to Google Calendar
     */
    public function addToGoogle(Event $event, CalendarService $calendarService)
    {
        $this->authorizeChurch($event);

        $url = $calendarService->getGoogleCalendarUrl($event);

        return redirect()->away($url);
    }

    /**
     * Matrix View — multi-week schedule grid across all service ministries
     */
    public function matrix(Request $request)
    {
        return redirect()->route('schedule', ['tab' => 'matrix']);
    }

    /**
     * Matrix View JSON data — events × ministries × roles/positions grid
     */
    public function matrixData(Request $request)
    {
        if (!auth()->user()->canView('events')) {
            abort(403);
        }

        $church = $this->getCurrentChurch();

        $serviceType = $request->get('service_type', 'sunday_service');
        $weeks = min((int) $request->get('weeks', 4), 12);
        $startDate = $request->get('start_date')
            ? Carbon::parse($request->get('start_date'))->startOfDay()
            : now()->startOfWeek(Carbon::MONDAY);
        $endDate = $startDate->copy()->addWeeks($weeks)->subDay()->endOfDay();

        $monthNames = ['', 'січ', 'лют', 'бер', 'кві', 'тра', 'чер', 'лип', 'сер', 'вер', 'жов', 'лис', 'гру'];

        // 1. Load events for the period
        $rawEvents = Event::where('church_id', $church->id)
            ->where('service_type', $serviceType)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        $events = $rawEvents->map(fn($e) => [
            'id' => $e->id,
            'title' => $e->title,
            'date' => $e->date->format('Y-m-d'),
            'dateLabel' => $e->date->format('j') . ' ' . $monthNames[$e->date->month],
            'dayOfWeek' => mb_substr($e->date->translatedFormat('D'), 0, 2),
            'time' => $e->time?->format('H:i') ?? '',
        ]);

        $eventIds = $events->pluck('id')->toArray();

        // 2. Load all service ministries (sunday_service_part + worship_ministry)
        $ministries = Ministry::where('church_id', $church->id)
            ->where(function ($q) {
                $q->where('is_sunday_service_part', true)
                  ->orWhere('is_worship_ministry', true);
            })
            ->orderBy('name')
            ->get();

        // 3. Build ministry data with roles OR positions
        $ministriesData = [];
        $grid = [];
        $members = [];

        foreach ($ministries as $ministry) {
            $roles = [];
            $hasMinistryRoles = $ministry->ministryRoles()->exists();

            if ($hasMinistryRoles) {
                // Role-based (worship/service teams)
                $roles = $ministry->ministryRoles()
                    ->orderBy('sort_order')
                    ->get()
                    ->map(fn($r) => [
                        'id' => $r->id,
                        'name' => $r->name,
                        'icon' => $r->icon,
                        'type' => 'ministry_role',
                    ])->values()->toArray();
            } else {
                // Position-based (rotation)
                $roles = $ministry->positions()
                    ->orderBy('sort_order')
                    ->get()
                    ->map(fn($p) => [
                        'id' => $p->id,
                        'name' => $p->name,
                        'icon' => null,
                        'type' => 'position',
                    ])->values()->toArray();
            }

            if (empty($roles)) {
                continue;
            }

            $ministriesData[] = [
                'id' => $ministry->id,
                'name' => $ministry->name,
                'color' => $ministry->color,
                'icon' => $ministry->icon,
                'roles' => $roles,
            ];

            // Initialize grid for this ministry
            $mKey = (string) $ministry->id;
            $grid[$mKey] = [];
            foreach ($roles as $role) {
                $rKey = $role['type'] . '_' . $role['id'];
                $grid[$mKey][$rKey] = [];
            }

            // Load members for this ministry
            $members[$mKey] = $ministry->members()
                ->orderBy('last_name')
                ->get()
                ->map(fn($m) => [
                    'id' => $m->id,
                    'name' => $m->full_name,
                    'short_name' => $m->first_name . ' ' . mb_substr($m->last_name ?? '', 0, 1) . '.',
                    'has_telegram' => (bool) $m->telegram_chat_id,
                ])->values()->toArray();
        }

        if (count($eventIds) > 0) {
            // 4a. Load EventMinistryTeam entries (role-based)
            $ministryIds = collect($ministriesData)->pluck('id')->toArray();
            $teamEntries = EventMinistryTeam::whereIn('event_id', $eventIds)
                ->whereIn('ministry_id', $ministryIds)
                ->with('person')
                ->get();

            $seen = [];
            foreach ($teamEntries as $entry) {
                $mKey = (string) $entry->ministry_id;
                $rKey = 'ministry_role_' . $entry->ministry_role_id;
                $eKey = (string) $entry->event_id;
                $dedup = $mKey . '-' . $rKey . '-' . $eKey . '-' . $entry->person_id;

                if (isset($seen[$dedup]) || !isset($grid[$mKey][$rKey])) {
                    continue;
                }
                $seen[$dedup] = true;

                if (!isset($grid[$mKey][$rKey][$eKey])) {
                    $grid[$mKey][$rKey][$eKey] = [];
                }

                $person = $entry->person;
                $grid[$mKey][$rKey][$eKey][] = [
                    'id' => $entry->id,
                    'person_id' => $entry->person_id,
                    'person_name' => $person
                        ? $person->first_name . ' ' . mb_substr($person->last_name ?? '', 0, 1) . '.'
                        : '?',
                    'status' => $entry->status,
                    'has_telegram' => (bool) $person?->telegram_chat_id,
                    'source' => 'ministry_team',
                    'notes' => $entry->notes,
                ];
            }

            // 4b. Load Assignment entries (position-based)
            $positionMinistryIds = collect($ministriesData)
                ->filter(fn($m) => collect($m['roles'])->contains('type', 'position'))
                ->pluck('id')
                ->toArray();

            if (!empty($positionMinistryIds)) {
                $assignments = Assignment::whereIn('event_id', $eventIds)
                    ->whereHas('position', fn($q) => $q->whereIn('ministry_id', $positionMinistryIds))
                    ->with(['person', 'position'])
                    ->get();

                foreach ($assignments as $assignment) {
                    $position = $assignment->position;
                    if (!$position) continue;

                    $mKey = (string) $position->ministry_id;
                    $rKey = 'position_' . $position->id;
                    $eKey = (string) $assignment->event_id;
                    $dedup = $mKey . '-' . $rKey . '-' . $eKey . '-' . $assignment->person_id;

                    if (isset($seen[$dedup]) || !isset($grid[$mKey][$rKey])) {
                        continue;
                    }
                    $seen[$dedup] = true;

                    if (!isset($grid[$mKey][$rKey][$eKey])) {
                        $grid[$mKey][$rKey][$eKey] = [];
                    }

                    $person = $assignment->person;
                    $grid[$mKey][$rKey][$eKey][] = [
                        'id' => $assignment->id,
                        'person_id' => $assignment->person_id,
                        'person_name' => $person
                            ? $person->first_name . ' ' . mb_substr($person->last_name ?? '', 0, 1) . '.'
                            : '?',
                        'status' => $assignment->status,
                        'has_telegram' => (bool) $person?->telegram_chat_id,
                        'source' => 'assignment',
                        'notes' => $assignment->notes,
                    ];
                }
            }
        }

        return response()->json(compact('events', 'ministriesData', 'grid', 'members'));
    }

}
