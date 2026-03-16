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
            'guests_present.*' => 'integer|exists:group_guests,id',
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

            // Count present members (guests are now separate)
            $allMembers = $group->members()->wherePivot('role', '!=', 'guest')->get();
            $membersPresent = 0;
            foreach ($allMembers as $member) {
                if (in_array($member->id, $presentIds)) {
                    $membersPresent++;
                }
            }

            $guestsPresent = count($guestsPresentIds);
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

            // Create attendance records for members
            foreach ($allMembers as $member) {
                AttendanceRecord::create([
                    'attendance_id' => $attendance->id,
                    'person_id' => $member->id,
                    'present' => in_array($member->id, $presentIds),
                    'checked_in_at' => in_array($member->id, $presentIds) ? now()->format('H:i') : null,
                ]);
            }

            // Create attendance records for group guests
            foreach ($group->guests as $guest) {
                DB::table('group_guest_attendance')->insert([
                    'group_guest_id' => $guest->id,
                    'attendance_id' => $attendance->id,
                    'present' => in_array($guest->id, $guestsPresentIds),
                    'created_at' => now(),
                    'updated_at' => now(),
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
        $group->load(['members', 'guests']);

        $guestAttendance = DB::table('group_guest_attendance')
            ->where('attendance_id', $attendance->id)
            ->get()
            ->keyBy('group_guest_id');

        return view('groups.attendance.show', compact('group', 'attendance', 'guestAttendance'));
    }

    public function edit(Group $group, Attendance $attendance)
    {
        $this->checkAttendanceEnabled();
        $this->authorize('update', $group);
        abort_unless($attendance->attendable_id === $group->id && $attendance->attendable_type === Group::class, 404);

        $group->load(['members', 'guests']);
        $attendance->load('records');

        $presentIds = $attendance->records->where('present', true)->pluck('person_id')->toArray();
        $guestPresentIds = DB::table('group_guest_attendance')
            ->where('attendance_id', $attendance->id)
            ->where('present', true)
            ->pluck('group_guest_id')
            ->toArray();

        return view('groups.attendance.edit', compact('group', 'attendance', 'presentIds', 'guestPresentIds'));
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
            'guests_present.*' => 'integer|exists:group_guests,id',
        ]);

        $presentIds = $validated['present'] ?? [];
        $guestsPresentIds = $validated['guests_present'] ?? [];
        $anonymousGuests = (int) ($validated['guests_count'] ?? 0);

        // Count present members (guests are now separate)
        $allMembers = $group->members()->wherePivot('role', '!=', 'guest')->get();
        $membersPresent = 0;
        foreach ($allMembers as $member) {
            if (in_array($member->id, $presentIds)) {
                $membersPresent++;
            }
        }

        $guestsPresent = count($guestsPresentIds);
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

        // Update group guest attendance records
        DB::table('group_guest_attendance')->where('attendance_id', $attendance->id)->delete();
        foreach ($group->guests as $guest) {
            DB::table('group_guest_attendance')->insert([
                'group_guest_id' => $guest->id,
                'attendance_id' => $attendance->id,
                'present' => in_array($guest->id, $guestsPresentIds),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

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

            // Create empty records for members (excluding old guest pivots)
            foreach ($group->members->filter(fn($m) => $m->pivot->role !== 'guest') as $member) {
                AttendanceRecord::create([
                    'attendance_id' => $attendance->id,
                    'person_id' => $member->id,
                    'present' => false,
                ]);
            }

            // Create empty records for group guests
            foreach ($group->guests as $guest) {
                DB::table('group_guest_attendance')->insert([
                    'group_guest_id' => $guest->id,
                    'attendance_id' => $attendance->id,
                    'present' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $attendance->load('records.person');

        return view('groups.attendance.checkin', compact('group', 'attendance'));
    }

    /**
     * Toggle person presence via AJAX (works for both members and group guests)
     */
    public function togglePresence(Request $request, Group $group, Attendance $attendance)
    {
        $this->checkAttendanceEnabled();
        $this->authorize('update', $group);
        abort_unless($attendance->attendable_id === $group->id && $attendance->attendable_type === Group::class, 404);

        // Check if toggling a group guest or a member
        if ($request->has('guest_id')) {
            $guestId = $request->input('guest_id');
            $existing = DB::table('group_guest_attendance')
                ->where('group_guest_id', $guestId)
                ->where('attendance_id', $attendance->id)
                ->first();

            if ($existing) {
                $newPresent = !$existing->present;
                DB::table('group_guest_attendance')
                    ->where('id', $existing->id)
                    ->update(['present' => $newPresent, 'updated_at' => now()]);
            } else {
                $newPresent = true;
                DB::table('group_guest_attendance')->insert([
                    'group_guest_id' => $guestId,
                    'attendance_id' => $attendance->id,
                    'present' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->recalculateCounts($attendance, $group);

            return response()->json([
                'success' => true,
                'present' => $newPresent,
                'members_present' => $attendance->members_present,
                'guests_count' => $attendance->guests_count,
                'total_count' => $attendance->total_count,
            ]);
        }

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
     * Recalculate attendance counts
     */
    private function recalculateCounts(Attendance $attendance, Group $group): void
    {
        $membersPresent = $attendance->records()->where('present', true)->count();
        $namedGuestsPresent = DB::table('group_guest_attendance')
            ->where('attendance_id', $attendance->id)
            ->where('present', true)
            ->count();
        $anonymousGuests = $attendance->anonymous_guests_count ?? 0;
        $totalGuests = $namedGuestsPresent + $anonymousGuests;

        $attendance->update([
            'members_present' => $membersPresent,
            'guests_count' => $totalGuests,
            'total_count' => $membersPresent + $totalGuests,
        ]);
    }
}
