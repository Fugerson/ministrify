<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ministry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $church = $this->getCurrentChurch();

        $query = Event::where('church_id', $church->id)
            ->with(['ministry', 'assignments.person', 'assignments.position']);

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
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
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
            ->with(['ministry', 'assignments.person', 'assignments.position'])
            ->orderBy('date')
            ->orderBy('time')
            ->get()
            ->groupBy(fn($e) => $e->date->format('Y-m-d'));

        $ministries = $church->ministries;

        return view('schedule.calendar', compact(
            'events', 'year', 'month', 'startDate', 'endDate',
            'ministries', 'view', 'currentWeek'
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
            'ministry_id' => 'required|exists:ministries,id',
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'notes' => 'nullable|string',
            'recurrence_rule' => 'nullable|string',
        ]);

        $church = $this->getCurrentChurch();
        $ministry = Ministry::findOrFail($validated['ministry_id']);

        // Check access
        if ($ministry->church_id !== $church->id) {
            abort(404);
        }

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

        $event->load([
            'ministry.positions',
            'assignments.person',
            'assignments.position',
        ]);

        // Get available people for unfilled positions
        $availablePeople = $event->ministry->members()
            ->whereDoesntHave('unavailableDates', function ($q) use ($event) {
                $q->where('date_from', '<=', $event->date)
                  ->where('date_to', '>=', $event->date);
            })
            ->get();

        return view('schedule.show', compact('event', 'availablePeople'));
    }

    public function edit(Event $event)
    {
        $this->authorizeChurch($event);
        Gate::authorize('manage-ministry', $event->ministry);

        $church = $this->getCurrentChurch();
        $ministries = Ministry::where('church_id', $church->id)->get();

        return view('schedule.edit', compact('event', 'ministries'));
    }

    public function update(Request $request, Event $event)
    {
        $this->authorizeChurch($event);
        Gate::authorize('manage-ministry', $event->ministry);

        $validated = $request->validate([
            'ministry_id' => 'required|exists:ministries,id',
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'notes' => 'nullable|string',
        ]);

        $event->update($validated);

        return redirect()->route('events.show', $event)
            ->with('success', 'Подію оновлено.');
    }

    public function destroy(Event $event)
    {
        $this->authorizeChurch($event);
        Gate::authorize('manage-ministry', $event->ministry);

        $event->delete();

        return redirect()->route('schedule')
            ->with('success', 'Подію видалено.');
    }

    public function mySchedule()
    {
        $user = auth()->user();

        if (!$user->person) {
            return redirect()->route('dashboard')
                ->with('error', 'Ваш профіль не знайдено.');
        }

        $assignments = $user->person->assignments()
            ->with(['event.ministry', 'position'])
            ->whereHas('event', fn($q) => $q->where('date', '>=', now()))
            ->get()
            ->sortBy(fn($a) => $a->event->date);

        return view('schedule.my-schedule', compact('assignments'));
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

    private function authorizeChurch(Event $event): void
    {
        if ($event->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
