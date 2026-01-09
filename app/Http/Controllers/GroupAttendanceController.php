<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupAttendanceController extends Controller
{
    protected function checkAttendanceEnabled(): void
    {
        $church = $this->getCurrentChurch();
        if (!$church->attendance_enabled) {
            abort(403, 'Функцію відвідуваності вимкнено для вашої церкви.');
        }
    }

    public function index(Group $group)
    {
        $this->checkAttendanceEnabled();
        $this->authorize('view', $group);

        $attendances = $group->attendances()
            ->with(['records.person', 'recorder'])
            ->orderByDesc('date')
            ->paginate(20);

        // Stats for charts
        $chartData = $group->attendances()
            ->orderBy('date')
            ->take(12)
            ->get()
            ->map(fn($a) => [
                'date' => $a->date->format('d.m'),
                'members' => $a->members_present,
                'guests' => $a->guests_count,
                'total' => $a->total_count,
            ]);

        return view('groups.attendance.index', compact('group', 'attendances', 'chartData'));
    }

    public function create(Group $group)
    {
        $this->checkAttendanceEnabled();
        $this->authorize('update', $group);

        $group->load('members');

        // Check if attendance already exists for today
        $existingToday = $group->attendances()->whereDate('date', today())->first();

        return view('groups.attendance.create', compact('group', 'existingToday'));
    }

    public function store(Request $request, Group $group)
    {
        $this->checkAttendanceEnabled();
        $this->authorize('update', $group);

        $validated = $request->validate([
            'date' => 'required|date',
            'time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'guests_count' => 'nullable|integer|min:0',
            'present' => 'nullable|array',
            'present.*' => 'exists:people,id',
        ]);

        $presentIds = $validated['present'] ?? [];

        return DB::transaction(function () use ($group, $validated, $presentIds) {
            // Check for duplicate with lock to prevent race condition
            $existing = $group->attendances()
                ->whereDate('date', $validated['date'])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return redirect()->route('groups.attendance.edit', [$group, $existing])
                    ->with('info', 'Відвідуваність за цю дату вже існує. Ви можете її редагувати.');
            }

            // Create unified attendance record
            $attendance = $group->createAttendance([
                'date' => $validated['date'],
                'time' => $validated['time'] ?? null,
                'location' => $validated['location'] ?? $group->meeting_location,
                'notes' => $validated['notes'] ?? null,
                'guests_count' => $validated['guests_count'] ?? 0,
                'members_present' => count($presentIds),
                'total_count' => count($presentIds) + ($validated['guests_count'] ?? 0),
                'recorded_by' => auth()->id(),
            ]);

            // Create attendance records
            foreach ($group->members as $member) {
                AttendanceRecord::create([
                    'attendance_id' => $attendance->id,
                    'person_id' => $member->id,
                    'present' => in_array($member->id, $presentIds),
                    'checked_in_at' => in_array($member->id, $presentIds) ? now()->format('H:i') : null,
                ]);
            }

            return redirect()->route('groups.show', $group)
                ->with('success', 'Відвідуваність записано');
        });
    }

    public function show(Group $group, Attendance $attendance)
    {
        $this->checkAttendanceEnabled();
        $this->authorize('view', $group);

        $attendance->load(['records.person', 'recorder']);

        return view('groups.attendance.show', compact('group', 'attendance'));
    }

    public function edit(Group $group, Attendance $attendance)
    {
        $this->checkAttendanceEnabled();
        $this->authorize('update', $group);

        $group->load('members');
        $attendance->load('records');

        $presentIds = $attendance->records->where('present', true)->pluck('person_id')->toArray();

        return view('groups.attendance.edit', compact('group', 'attendance', 'presentIds'));
    }

    public function update(Request $request, Group $group, Attendance $attendance)
    {
        $this->checkAttendanceEnabled();
        $this->authorize('update', $group);

        $validated = $request->validate([
            'date' => 'required|date',
            'time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'guests_count' => 'nullable|integer|min:0',
            'present' => 'nullable|array',
            'present.*' => 'exists:people,id',
        ]);

        $presentIds = $validated['present'] ?? [];

        $attendance->update([
            'date' => $validated['date'],
            'time' => $validated['time'] ?? null,
            'location' => $validated['location'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'guests_count' => $validated['guests_count'] ?? 0,
            'members_present' => count($presentIds),
            'total_count' => count($presentIds) + ($validated['guests_count'] ?? 0),
        ]);

        // Update records
        foreach ($attendance->records as $record) {
            $record->update([
                'present' => in_array($record->person_id, $presentIds),
            ]);
        }

        // Add new members if any
        $existingPersonIds = $attendance->records->pluck('person_id')->toArray();
        foreach ($group->members as $member) {
            if (!in_array($member->id, $existingPersonIds)) {
                AttendanceRecord::create([
                    'attendance_id' => $attendance->id,
                    'person_id' => $member->id,
                    'present' => in_array($member->id, $presentIds),
                ]);
            }
        }

        return redirect()->route('groups.show', $group)
            ->with('success', 'Відвідуваність оновлено');
    }

    public function destroy(Group $group, Attendance $attendance)
    {
        $this->checkAttendanceEnabled();
        $this->authorize('update', $group);

        $attendance->delete();

        return back()->with('success', 'Запис відвідуваності видалено');
    }

    /**
     * Quick check-in for today's meeting
     */
    public function quickCheckin(Group $group)
    {
        $this->checkAttendanceEnabled();
        $this->authorize('update', $group);

        $group->load('members');

        // Get or create today's attendance
        $attendance = $group->attendances()->whereDate('date', today())->first();

        if (!$attendance) {
            $attendance = $group->createAttendance([
                'date' => today(),
                'time' => now()->format('H:i'),
                'location' => $group->meeting_location,
                'recorded_by' => auth()->id(),
            ]);

            // Create empty records for all members
            foreach ($group->members as $member) {
                AttendanceRecord::create([
                    'attendance_id' => $attendance->id,
                    'person_id' => $member->id,
                    'present' => false,
                ]);
            }
        }

        $attendance->load('records.person');

        return view('groups.attendance.checkin', compact('group', 'attendance'));
    }

    /**
     * Toggle member presence via AJAX
     */
    public function togglePresence(Request $request, Group $group, Attendance $attendance)
    {
        $this->checkAttendanceEnabled();
        $this->authorize('update', $group);

        $validated = $request->validate([
            'person_id' => 'required|exists:people,id',
        ]);

        $record = $attendance->records()->where('person_id', $validated['person_id'])->first();

        if ($record) {
            $record->update([
                'present' => !$record->present,
                'checked_in_at' => !$record->present ? now()->format('H:i') : null,
            ]);
        } else {
            $record = AttendanceRecord::create([
                'attendance_id' => $attendance->id,
                'person_id' => $validated['person_id'],
                'present' => true,
                'checked_in_at' => now()->format('H:i'),
            ]);
        }

        // Update attendance counts
        $attendance->recalculateCounts();

        return response()->json([
            'success' => true,
            'present' => $record->present,
            'members_present' => $attendance->members_present,
        ]);
    }
}
