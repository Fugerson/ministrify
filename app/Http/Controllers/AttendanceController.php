<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $church = $this->getCurrentChurch();

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $attendances = Attendance::where('church_id', $church->id)
            ->forMonth($year, $month)
            ->with('event.ministry')
            ->orderByDesc('date')
            ->paginate(20);

        return view('attendance.index', compact('attendances', 'year', 'month'));
    }

    public function create(Request $request)
    {
        $church = $this->getCurrentChurch();

        $eventId = $request->get('event');
        $event = null;
        $date = now();

        if ($eventId) {
            $event = Event::where('church_id', $church->id)->findOrFail($eventId);
            $date = $event->date;
        }

        $people = Person::where('church_id', $church->id)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('attendance.create', compact('people', 'event', 'date'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'event_id' => 'nullable|exists:events,id',
            'total_count' => 'required|integer|min:0',
            'notes' => 'nullable|string',
            'present' => 'nullable|array',
        ]);

        $church = $this->getCurrentChurch();

        // Check if event belongs to church
        if (!empty($validated['event_id'])) {
            $event = Event::findOrFail($validated['event_id']);
            if ($event->church_id !== $church->id) {
                abort(404);
            }
        }

        $attendance = Attendance::create([
            'church_id' => $church->id,
            'event_id' => $validated['event_id'] ?? null,
            'date' => $validated['date'],
            'total_count' => $validated['total_count'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Create attendance records
        if (!empty($validated['present'])) {
            foreach ($validated['present'] as $personId) {
                AttendanceRecord::create([
                    'attendance_id' => $attendance->id,
                    'person_id' => $personId,
                    'present' => true,
                ]);
            }
        }

        return redirect()->route('attendance.show', $attendance)
            ->with('success', 'Відвідуваність збережено.');
    }

    public function show(Attendance $attendance)
    {
        $this->authorizeChurch($attendance);

        $attendance->load(['event.ministry', 'records.person']);

        return view('attendance.show', compact('attendance'));
    }

    public function edit(Attendance $attendance)
    {
        $this->authorizeChurch($attendance);

        $church = $this->getCurrentChurch();

        $people = Person::where('church_id', $church->id)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $presentIds = $attendance->records()->where('present', true)->pluck('person_id')->toArray();

        return view('attendance.edit', compact('attendance', 'people', 'presentIds'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $this->authorizeChurch($attendance);

        $validated = $request->validate([
            'total_count' => 'required|integer|min:0',
            'notes' => 'nullable|string',
            'present' => 'nullable|array',
        ]);

        $attendance->update([
            'total_count' => $validated['total_count'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Update attendance records
        $attendance->records()->delete();

        if (!empty($validated['present'])) {
            foreach ($validated['present'] as $personId) {
                AttendanceRecord::create([
                    'attendance_id' => $attendance->id,
                    'person_id' => $personId,
                    'present' => true,
                ]);
            }
        }

        return redirect()->route('attendance.show', $attendance)
            ->with('success', 'Відвідуваність оновлено.');
    }

    public function destroy(Attendance $attendance)
    {
        $this->authorizeChurch($attendance);

        $attendance->delete();

        return back()->with('success', 'Запис видалено.');
    }

    public function stats(Request $request)
    {
        $church = $this->getCurrentChurch();

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        // Average attendance this month
        $monthlyAttendance = Attendance::where('church_id', $church->id)
            ->forMonth($year, $month)
            ->avg('total_count');

        // Chart data (last 12 weeks)
        $chartData = [];
        for ($i = 11; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek(Carbon::SUNDAY);
            $weekEnd = $weekStart->copy()->endOfWeek();

            $count = Attendance::where('church_id', $church->id)
                ->whereBetween('date', [$weekStart, $weekEnd])
                ->avg('total_count') ?? 0;

            $chartData[] = [
                'date' => $weekStart->format('d.m'),
                'count' => round($count),
            ];
        }

        // People needing attention (not attended for 3+ weeks)
        $threeWeeksAgo = now()->subWeeks(3);
        $needAttention = Person::where('church_id', $church->id)
            ->whereDoesntHave('attendanceRecords', function ($q) use ($threeWeeksAgo) {
                $q->whereHas('attendance', fn($aq) => $aq->where('date', '>=', $threeWeeksAgo))
                  ->where('present', true);
            })
            ->orderBy('last_name')
            ->get();

        return view('attendance.stats', compact('monthlyAttendance', 'chartData', 'needAttention', 'year', 'month'));
    }

    private function authorizeChurch(Attendance $attendance): void
    {
        if ($attendance->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
