<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\Board;
use App\Models\Event;
use App\Models\Ministry;
use App\Models\MinistryMeeting;
use App\Rules\BelongsToChurch;
use App\Services\CalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $church = $this->getCurrentChurch();

        $query = Event::where('church_id', $church->id)
            ->with(['ministry', 'responsibilities.person']);

        if ($ministryId = $request->get('ministry')) {
            $query->where('ministry_id', $ministryId);
        }

        $events = $query->upcoming()->paginate(20);
        $ministries = $church->ministries;

        return view('schedule.index', compact('events', 'ministries'));
    }

    public function schedule(Request $request)
    {
        $church = $this->getCurrentChurch();

        $view = $request->get('view', 'month');
        $year = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $week = $request->get('week');

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
        }

        $events = Event::where('church_id', $church->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['ministry', 'responsibilities.person'])
            ->orderBy('date')
            ->orderBy('time')
            ->get();

        // Get ministry meetings for the same period
        $meetings = MinistryMeeting::whereHas('ministry', fn($q) => $q->where('church_id', $church->id))
            ->whereBetween('date', [$startDate, $endDate])
            ->with('ministry')
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        // Combine events and meetings into unified collection
        $calendarItems = collect();

        foreach ($events as $event) {
            $calendarItems->push((object)[
                'type' => 'event',
                'id' => $event->id,
                'title' => $event->title,
                'date' => $event->date,
                'time' => $event->time,
                'ministry' => $event->ministry,
                'ministry_id' => $event->ministry_id,
                'original' => $event,
            ]);
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
                    'original' => $event,
                ]);
            }
        }

        $ministries = $church->ministries;

        // Calculate next month for display
        $nextMonth = $month == 12 ? 1 : $month + 1;
        $nextYear = $month == 12 ? $year + 1 : $year;

        return view('schedule.calendar', compact(
            'events', 'year', 'month', 'startDate', 'endDate',
            'ministries', 'view', 'currentWeek', 'church', 'meetings', 'upcomingNextMonth', 'nextMonth', 'nextYear'
        ));
    }

    public function calendar(Request $request)
    {
        return $this->schedule($request);
    }

    public function create(Request $request)
    {
        $church = $this->getCurrentChurch();
        $user = auth()->user();

        $query = Ministry::where('church_id', $church->id);

        if ($user->isLeader() && $user->person) {
            $query->where('leader_id', $user->person->id);
        }

        $ministries = $query->with('positions')->get();

        $selectedMinistry = $request->get('ministry');

        return view('schedule.create', compact('ministries', 'selectedMinistry'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ministry_id' => ['required', 'exists:ministries,id', new BelongsToChurch(Ministry::class)],
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'notes' => 'nullable|string',
            'recurrence_rule' => 'nullable|string',
            'is_service' => 'nullable|boolean',
            'service_type' => 'nullable|string|in:sunday_service,youth_meeting,prayer_meeting,special_service',
            'track_attendance' => 'nullable|boolean',
        ]);

        $validated['is_service'] = $request->boolean('is_service');
        $validated['track_attendance'] = $request->boolean('track_attendance');

        $church = $this->getCurrentChurch();
        $ministry = Ministry::findOrFail($validated['ministry_id']);

        // Authorization already validated by BelongsToChurch rule
        Gate::authorize('manage-ministry', $ministry);

        $validated['church_id'] = $church->id;
        $event = Event::create($validated);

        // Handle recurring events
        if (!empty($validated['recurrence_rule'])) {
            $this->generateRecurringEvents($event, $validated['recurrence_rule']);
        }

        return redirect()->route('events.show', $event)
            ->with('success', 'Подію створено.');
    }

    public function show(Event $event)
    {
        $this->authorizeChurch($event);
        $church = $this->getCurrentChurch();

        $event->load([
            'ministry',
            'checklist.items.completedByUser',
            'planItems.responsible',
            'responsibilities.person',
            'attendance.records.person',
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

            // Build blockout info for each person
            foreach ($availablePeople as $person) {
                if ($person->hasBlockoutOn($event->date, $event->ministry_id)) {
                    $volunteerBlockouts[$person->id] = $person->getBlockoutReasonFor($event->date, $event->ministry_id);
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

        // Get ministries for inline editing
        $ministries = Ministry::where('church_id', $church->id)->get();

        return view('schedule.show', compact('event', 'availablePeople', 'volunteerBlockouts', 'checklistTemplates', 'boards', 'allPeople', 'ministries'));
    }

    public function edit(Event $event)
    {
        $this->authorizeChurch($event);

        // Authorize ministry management if event has ministry
        if ($event->ministry) {
            Gate::authorize('manage-ministry', $event->ministry);
        } elseif (!$this->isAdmin()) {
            abort(403, 'Тільки адміністратор може редагувати події без служіння.');
        }

        $church = $this->getCurrentChurch();
        $ministries = Ministry::where('church_id', $church->id)->get();

        return view('schedule.edit', compact('event', 'ministries'));
    }

    public function update(Request $request, Event $event)
    {
        $this->authorizeChurch($event);

        // Authorize ministry management if event has ministry
        if ($event->ministry) {
            Gate::authorize('manage-ministry', $event->ministry);
        } elseif (!$this->isAdmin()) {
            abort(403, 'Тільки адміністратор може редагувати події без служіння.');
        }

        // Support partial updates for AJAX
        $rules = [
            'ministry_id' => ['sometimes', 'exists:ministries,id', new BelongsToChurch(Ministry::class)],
            'title' => 'sometimes|string|max:255',
            'date' => 'sometimes|date',
            'time' => 'sometimes|date_format:H:i',
            'notes' => 'nullable|string',
            'is_service' => 'nullable|boolean',
            'service_type' => 'nullable|string|in:sunday_service,youth_meeting,prayer_meeting,special_service',
            'track_attendance' => 'nullable|boolean',
        ];

        $validated = $request->validate($rules);

        if ($request->has('is_service')) {
            $validated['is_service'] = $request->boolean('is_service');
        }
        if ($request->has('track_attendance')) {
            $validated['track_attendance'] = $request->boolean('track_attendance');
        }

        $event->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Подію оновлено',
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

        return redirect()->route('events.show', $event)
            ->with('success', 'Подію оновлено.');
    }

    public function destroy(Event $event)
    {
        $this->authorizeChurch($event);

        // Authorize ministry management if event has ministry
        if ($event->ministry) {
            Gate::authorize('manage-ministry', $event->ministry);
        } elseif (!$this->isAdmin()) {
            abort(403, 'Тільки адміністратор може видаляти події без служіння.');
        }

        $event->delete();

        return back()->with('success', 'Подію видалено.');
    }

    public function saveAttendance(Request $request, Event $event)
    {
        $this->authorizeChurch($event);

        if (!$event->track_attendance) {
            abort(404);
        }

        $validated = $request->validate([
            'present' => 'nullable|array',
            'present.*' => 'integer|exists:people,id',
            'guests_count' => 'nullable|integer|min:0',
        ]);

        $church = $this->getCurrentChurch();

        // Get or create attendance record for this event
        $attendance = Attendance::firstOrCreate(
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

        $presentIds = $validated['present'] ?? [];
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

        return response()->json(['success' => true]);
    }

    public function mySchedule()
    {
        $user = auth()->user();

        if (!$user->person) {
            return redirect()->route('dashboard')
                ->with('error', 'Ваш профіль не знайдено.');
        }

        $responsibilities = $user->person->responsibilities()
            ->with(['event.ministry'])
            ->whereHas('event', fn($q) => $q->where('date', '>=', now()))
            ->get()
            ->sortBy(fn($r) => $r->event->date);

        return view('schedule.my-schedule', compact('responsibilities'));
    }

    private function generateRecurringEvents(Event $parentEvent, string $rule): void
    {
        // Simple weekly recurrence for 4 weeks
        if ($rule === 'weekly') {
            for ($i = 1; $i <= 4; $i++) {
                Event::create([
                    'church_id' => $parentEvent->church_id,
                    'ministry_id' => $parentEvent->ministry_id,
                    'title' => $parentEvent->title,
                    'date' => $parentEvent->date->copy()->addWeeks($i),
                    'time' => $parentEvent->time,
                    'notes' => $parentEvent->notes,
                    'parent_event_id' => $parentEvent->id,
                ]);
            }
        }
    }

    /**
     * Export calendar to iCal format
     */
    public function exportIcal(Request $request, CalendarService $calendarService)
    {
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
        $ministry = Ministry::findOrFail($request->ministry_id);

        if ($ministry->church_id !== $church->id) {
            abort(404);
        }

        Gate::authorize('manage-ministry', $ministry);

        $result = $calendarService->importFromIcal($request->file('file'), $church, $ministry);

        $message = "Імпортовано подій: {$result['total_imported']}";
        if ($result['total_skipped'] > 0) {
            $message .= ", пропущено: {$result['total_skipped']}";
        }
        if ($result['total_errors'] > 0) {
            $message .= ", помилок: {$result['total_errors']}";
        }

        return redirect()->route('schedule')
            ->with('success', $message)
            ->with('import_details', $result);
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
     * Import events from Google Calendar URL
     */
    public function importFromUrl(Request $request, CalendarService $calendarService)
    {
        $request->validate([
            'calendar_url' => 'required|url',
            'ministry_id' => 'required|exists:ministries,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'save_settings' => 'nullable|boolean',
        ]);

        $church = $this->getCurrentChurch();
        $ministry = Ministry::findOrFail($request->ministry_id);

        if ($ministry->church_id !== $church->id) {
            abort(404);
        }

        Gate::authorize('manage-ministry', $ministry);

        // Save settings for quick sync if requested
        if ($request->save_settings) {
            $church->setSetting('google_calendar_url', $request->calendar_url);
            $church->setSetting('google_calendar_ministry_id', $request->ministry_id);
        }

        try {
            $result = $calendarService->importFromUrl(
                $request->calendar_url,
                $church,
                $ministry,
                $request->start_date ? Carbon::parse($request->start_date) : null,
                $request->end_date ? Carbon::parse($request->end_date) : null
            );

            // Update last sync time
            $church->setSetting('google_calendar_last_sync', now()->toIso8601String());

            $message = "Синхронізовано подій: {$result['total_imported']}";
            if ($result['total_skipped'] > 0) {
                $message .= ", пропущено (дублікати): {$result['total_skipped']}";
            }
            if ($result['total_errors'] > 0) {
                $message .= ", помилок: {$result['total_errors']}";
            }

            return redirect()->route('schedule')
                ->with('success', $message)
                ->with('import_details', $result);

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['calendar_url' => 'Помилка завантаження календаря: ' . $e->getMessage()]);
        }
    }

    /**
     * Quick sync with saved Google Calendar settings
     */
    public function quickSync(CalendarService $calendarService)
    {
        $church = $this->getCurrentChurch();

        $url = $church->getSetting('google_calendar_url');
        $ministryId = $church->getSetting('google_calendar_ministry_id');

        if (!$url || !$ministryId) {
            return redirect()->route('calendar.import')
                ->with('info', 'Спочатку налаштуйте синхронізацію з Google Calendar');
        }

        $ministry = Ministry::find($ministryId);
        if (!$ministry || $ministry->church_id !== $church->id) {
            return redirect()->route('calendar.import')
                ->with('error', 'Служіння для синхронізації не знайдено. Налаштуйте заново.');
        }

        try {
            $result = $calendarService->importFromUrl(
                $url,
                $church,
                $ministry,
                now()->subMonth(),
                now()->addMonths(3)
            );

            $church->setSetting('google_calendar_last_sync', now()->toIso8601String());

            $message = "Синхронізовано подій: {$result['total_imported']}";
            if ($result['total_skipped'] > 0) {
                $message .= ", пропущено (дублікати): {$result['total_skipped']}";
            }

            return redirect()->route('schedule')->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->route('schedule')
                ->with('error', 'Помилка синхронізації: ' . $e->getMessage());
        }
    }

    /**
     * Save Google Calendar sync settings
     */
    public function saveGoogleSettings(Request $request)
    {
        $request->validate([
            'google_calendar_url' => 'required|url',
            'google_calendar_ministry_id' => 'required|exists:ministries,id',
        ]);

        $church = $this->getCurrentChurch();

        $church->setSetting('google_calendar_url', $request->google_calendar_url);
        $church->setSetting('google_calendar_ministry_id', $request->google_calendar_ministry_id);

        return response()->json(['success' => true]);
    }

    /**
     * Remove Google Calendar sync settings
     */
    public function removeGoogleSettings()
    {
        $church = $this->getCurrentChurch();

        $settings = $church->settings ?? [];
        unset($settings['google_calendar_url']);
        unset($settings['google_calendar_ministry_id']);
        unset($settings['google_calendar_last_sync']);
        $church->settings = $settings;
        $church->save();

        return redirect()->route('schedule')->with('success', 'Налаштування синхронізації видалено');
    }
}
