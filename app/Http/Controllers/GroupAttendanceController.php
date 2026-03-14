<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

        // Stats for charts — get newest 12 records, then reverse for chronological display
        $chartData = $group->attendances()
            ->orderByDesc('date')
            ->take(12)
            ->get()
            ->reverse()
            ->values()
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
            'notes' => 'nullable|string|max:5000',
            'guests_count' => 'nullable|integer|min:0',
            'present' => 'nullable|array',
            'present.*' => [Rule::exists('people', 'id')->where('church_id', $this->getCurrentChurch()->id)],
        ]);

        $presentIds = $validated['present'] ?? [];

        return DB::transaction(function () use ($request, $group, $validated, $presentIds) {
            // Check for duplicate with lock to prevent race condition
            $existing = $group->attendances()
                ->whereDate('date', $validated['date'])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Відвідуваність за цю дату вже існує. Ви можете її редагувати.',
                        'redirect_url' => route('groups.attendance.edit', [$group, $existing]),
                    ], 422);
                }
                return redirect()->route('groups.attendance.edit', [$group, $existing])
                    ->with('info', 'Відвідуваність за цю дату вже існує. Ви можете її редагувати.');
            }

            $anonymousGuests = (int) ($validated['guests_count'] ?? 0);

            // Count present members vs guests
            $allMembers = $group->members()->get();
            $membersPresent = 0;
            $guestsPresent = 0;
            foreach ($allMembers as $member) {
                if (in_array($member->id, $presentIds)) {
                    if ($member->pivot->role === Group::ROLE_GUEST) {
                        $guestsPresent++;
                    } else {
                        $membersPresent++;
                    }
                }
            }

            $totalGuests = $guestsPresent + $anonymousGuests;

            // Create unified attendance record
            $attendance = $group->createAttendance([
                'date' => $validated['date'],
                'time' => $validated['time'] ?? null,
                'location' => $validated['location'] ?? $group->location,
                'notes' => $validated['notes'] ?? null,
                'guests_count' => $totalGuests,
                'anonymous_guests_count' => $anonymousGuests,
                'members_present' => $membersPresent,
                'total_count' => $membersPresent + $totalGuests,
                'recorded_by' => auth()->id(),
            ]);

            // Create attendance records for ALL members (including guests)
            foreach ($allMembers as $member) {
                AttendanceRecord::create([
                    'attendance_id' => $attendance->id,
                    'person_id' => $member->id,
                    'present' => in_array($member->id, $presentIds),
                    'checked_in_at' => in_array($member->id, $presentIds) ? now()->format('H:i') : null,
                ]);
            }

            return $this->successResponse($request, 'Відвідуваність записано.', 'groups.show', ['group' => $group->id]);
        });
    }

    public function show(Group $group, Attendance $attendance)
    {
        $this->checkAttendanceEnabled();
        $this->authorize('view', $group);
        abort_unless($attendance->attendable_id === $group->id && $attendance->attendable_type === Group::class, 404);

        $attendance->load(['records.person', 'recorder']);
        $group->load('members');

        return view('groups.attendance.show', compact('group', 'attendance'));
    }

    public function edit(Group $group, Attendance $attendance)
    {
        $this->checkAttendanceEnabled();
        $this->authorize('update', $group);
        abort_unless($attendance->attendable_id === $group->id && $attendance->attendable_type === Group::class, 404);

        $group->load('members');
        $attendance->load('records');

        $presentIds = $attendance->records->where('present', true)->pluck('person_id')->toArray();

        return view('groups.attendance.edit', compact('group', 'attendance', 'presentIds'));
    }

    public function update(Request $request, Group $group, Attendance $attendance)
    {
        $this->checkAttendanceEnabled();
        $this->authorize('update', $group);
        abort_unless($attendance->attendable_id === $group->id && $attendance->attendable_type === Group::class, 404);

        $validated = $request->validate([
            'date' => 'required|date',
            'time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:5000',
            'guests_count' => 'nullable|integer|min:0',
            'present' => 'nullable|array',
            'present.*' => [Rule::exists('people', 'id')->where('church_id', $this->getCurrentChurch()->id)],
        ]);

        $presentIds = $validated['present'] ?? [];
        $anonymousGuests = (int) ($validated['guests_count'] ?? 0);

        // Count present members vs guests
        $allMembers = $group->members()->get();
        $membersPresent = 0;
        $guestsPresent = 0;
        foreach ($allMembers as $member) {
            if (in_array($member->id, $presentIds)) {
                if ($member->pivot->role === Group::ROLE_GUEST) {
                    $guestsPresent++;
                } else {
                    $membersPresent++;
                }
            }
        }

        $totalGuests = $guestsPresent + $anonymousGuests;

        $attendance->update([
            'date' => $validated['date'],
            'time' => $validated['time'] ?? null,
            'location' => $validated['location'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'guests_count' => $totalGuests,
            'anonymous_guests_count' => $anonymousGuests,
            'members_present' => $membersPresent,
            'total_count' => $membersPresent + $totalGuests,
        ]);

        // Update records
        foreach ($attendance->records as $record) {
            $record->update([
                'present' => in_array($record->person_id, $presentIds),
            ]);
        }

        // Add new members if any
        $existingPersonIds = $attendance->records->pluck('person_id')->toArray();
        foreach ($allMembers as $member) {
            if (!in_array($member->id, $existingPersonIds)) {
                AttendanceRecord::updateOrCreate(
                    [
                        'attendance_id' => $attendance->id,
                        'person_id' => $member->id,
                    ],
                    [
                        'present' => in_array($member->id, $presentIds),
                    ]
                );
            }
        }

        return $this->successResponse($request, 'Відвідуваність оновлено.', 'groups.show', ['group' => $group->id]);
    }

    public function destroy(Request $request, Group $group, Attendance $attendance)
    {
        $this->checkAttendanceEnabled();
        $this->authorize('update', $group);
        abort_unless($attendance->attendable_id === $group->id && $attendance->attendable_type === Group::class, 404);

        $attendance->delete();

        return $this->successResponse($request, 'Запис відвідуваності видалено.', 'groups.show', ['group' => $group->id]);
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
                'location' => $group->location,
                'recorded_by' => auth()->id(),
            ]);

            // Create empty records for ALL members (including guests)
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
     * Toggle person presence via AJAX (works for both members and guests)
     */
    public function togglePresence(Request $request, Group $group, Attendance $attendance)
    {
        $this->checkAttendanceEnabled();
        $this->authorize('update', $group);
        abort_unless($attendance->attendable_id === $group->id && $attendance->attendable_type === Group::class, 404);

        $validated = $request->validate([
            'person_id' => ['required', new \App\Rules\BelongsToChurch(\App\Models\Person::class)],
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

        // Recalculate counts
        $this->recalculateCounts($attendance, $group);

        return response()->json([
            'success' => true,
            'present' => $record->present,
            'members_present' => $attendance->members_present,
            'guests_count' => $attendance->guests_count,
            'total_count' => $attendance->total_count,
        ]);
    }

    /**
     * Recalculate attendance counts based on roles
     */
    private function recalculateCounts(Attendance $attendance, Group $group): void
    {
        // Get guest person IDs for this group
        $guestPersonIds = $group->guests()->pluck('people.id')->toArray();

        $allPresent = $attendance->records()->where('present', true)->pluck('person_id')->toArray();

        $membersPresent = count(array_diff($allPresent, $guestPersonIds));
        $namedGuestsPresent = count(array_intersect($allPresent, $guestPersonIds));
        $anonymousGuests = $attendance->anonymous_guests_count ?? 0;
        $totalGuests = $namedGuestsPresent + $anonymousGuests;

        $attendance->update([
            'members_present' => $membersPresent,
            'guests_count' => $totalGuests,
            'total_count' => $membersPresent + $totalGuests,
        ]);
    }
}
