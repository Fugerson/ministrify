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

        $group->load(['members', 'guests']);

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
            'guests_present' => 'nullable|array',
            'guests_present.*' => ['integer', Rule::exists('group_guests', 'id')->where('group_id', $group->id)],
        ]);

        $presentIds = $validated['present'] ?? [];
        $guestsPresentIds = $validated['guests_present'] ?? [];

        return DB::transaction(function () use ($request, $group, $validated, $presentIds, $guestsPresentIds) {
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
            $namedGuestsPresent = count($guestsPresentIds);
            $guestsCount = $namedGuestsPresent + $anonymousGuests;

            // Create unified attendance record
            $attendance = $group->createAttendance([
                'date' => $validated['date'],
                'time' => $validated['time'] ?? null,
                'location' => $validated['location'] ?? $group->location,
                'notes' => $validated['notes'] ?? null,
                'guests_count' => $guestsCount,
                'anonymous_guests_count' => $anonymousGuests,
                'members_present' => count($presentIds),
                'total_count' => count($presentIds) + $guestsCount,
                'recorded_by' => auth()->id(),
            ]);

            // Create attendance records for members
            foreach ($group->members as $member) {
                AttendanceRecord::create([
                    'attendance_id' => $attendance->id,
                    'person_id' => $member->id,
                    'present' => in_array($member->id, $presentIds),
                    'checked_in_at' => in_array($member->id, $presentIds) ? now()->format('H:i') : null,
                ]);
            }

            // Create attendance records for guests
            foreach ($group->guests as $guest) {
                $attendance->guestAttendances()->attach($guest->id, [
                    'present' => in_array($guest->id, $guestsPresentIds),
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

        $attendance->load(['records.person', 'recorder', 'guestAttendances']);

        return view('groups.attendance.show', compact('group', 'attendance'));
    }

    public function edit(Group $group, Attendance $attendance)
    {
        $this->checkAttendanceEnabled();
        $this->authorize('update', $group);
        abort_unless($attendance->attendable_id === $group->id && $attendance->attendable_type === Group::class, 404);

        $group->load(['members', 'guests']);
        $attendance->load(['records', 'guestAttendances']);

        $presentIds = $attendance->records->where('present', true)->pluck('person_id')->toArray();
        $guestsPresentIds = $attendance->guestAttendances->where('pivot.present', true)->pluck('id')->toArray();

        return view('groups.attendance.edit', compact('group', 'attendance', 'presentIds', 'guestsPresentIds'));
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
            'guests_present' => 'nullable|array',
            'guests_present.*' => ['integer', Rule::exists('group_guests', 'id')->where('group_id', $group->id)],
        ]);

        $presentIds = $validated['present'] ?? [];
        $guestsPresentIds = $validated['guests_present'] ?? [];
        $anonymousGuests = (int) ($validated['guests_count'] ?? 0);
        $namedGuestsPresent = count($guestsPresentIds);
        $guestsCount = $namedGuestsPresent + $anonymousGuests;

        $attendance->update([
            'date' => $validated['date'],
            'time' => $validated['time'] ?? null,
            'location' => $validated['location'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'guests_count' => $guestsCount,
            'anonymous_guests_count' => $anonymousGuests,
            'members_present' => count($presentIds),
            'total_count' => count($presentIds) + $guestsCount,
        ]);

        // Update records
        foreach ($attendance->records as $record) {
            $record->update([
                'present' => in_array($record->person_id, $presentIds),
            ]);
        }

        // Add new members if any (updateOrCreate prevents duplicates)
        $existingPersonIds = $attendance->records->pluck('person_id')->toArray();
        foreach ($group->members as $member) {
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

        // Sync guest attendance
        $group->load('guests');
        $syncData = [];
        foreach ($group->guests as $guest) {
            $syncData[$guest->id] = ['present' => in_array($guest->id, $guestsPresentIds)];
        }
        $attendance->guestAttendances()->sync($syncData);

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

        $group->load(['members', 'guests']);

        // Get or create today's attendance
        $attendance = $group->attendances()->whereDate('date', today())->first();

        if (!$attendance) {
            $attendance = $group->createAttendance([
                'date' => today(),
                'time' => now()->format('H:i'),
                'location' => $group->location,
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

            // Create empty records for all guests
            foreach ($group->guests as $guest) {
                $attendance->guestAttendances()->attach($guest->id, ['present' => false]);
            }
        }

        $attendance->load(['records.person', 'guestAttendances']);

        return view('groups.attendance.checkin', compact('group', 'attendance'));
    }

    /**
     * Toggle member presence via AJAX
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

        // Update attendance counts
        $this->recalculateWithGuests($attendance);

        return response()->json([
            'success' => true,
            'present' => $record->present,
            'members_present' => $attendance->members_present,
            'guests_count' => $attendance->guests_count,
            'total_count' => $attendance->total_count,
        ]);
    }

    /**
     * Toggle guest presence via AJAX
     */
    public function toggleGuestPresence(Request $request, Group $group, Attendance $attendance)
    {
        $this->checkAttendanceEnabled();
        $this->authorize('update', $group);
        abort_unless($attendance->attendable_id === $group->id && $attendance->attendable_type === Group::class, 404);

        $validated = $request->validate([
            'guest_id' => ['required', 'integer', Rule::exists('group_guests', 'id')->where('group_id', $group->id)],
        ]);

        $guestId = $validated['guest_id'];
        $existing = $attendance->guestAttendances()->where('group_guest_id', $guestId)->first();

        if ($existing) {
            $newPresent = !$existing->pivot->present;
            $attendance->guestAttendances()->updateExistingPivot($guestId, ['present' => $newPresent]);
        } else {
            $newPresent = true;
            $attendance->guestAttendances()->attach($guestId, ['present' => true]);
        }

        // Recalculate counts
        $this->recalculateWithGuests($attendance);

        return response()->json([
            'success' => true,
            'present' => $newPresent,
            'guests_count' => $attendance->guests_count,
            'members_present' => $attendance->members_present,
            'total_count' => $attendance->total_count,
        ]);
    }

    /**
     * Recalculate attendance counts including guests
     */
    private function recalculateWithGuests(Attendance $attendance): void
    {
        $membersPresent = $attendance->records()->where('present', true)->count();
        $namedGuestsPresent = $attendance->guestAttendances()->withoutTrashed()->wherePivot('present', true)->count();
        $anonymousGuests = $attendance->anonymous_guests_count ?? 0;
        $totalGuests = $namedGuestsPresent + $anonymousGuests;

        $attendance->update([
            'members_present' => $membersPresent,
            'guests_count' => $totalGuests,
            'total_count' => $membersPresent + $totalGuests,
        ]);
    }
}
