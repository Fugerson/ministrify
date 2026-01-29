<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\Person;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QrCheckinController extends Controller
{
    /**
     * Show the QR scanner page (for admins/leaders)
     */
    public function scanner()
    {
        return view('checkin.scanner');
    }

    /**
     * Show the check-in page for a specific event
     */
    public function show(string $token)
    {
        $event = Event::findByCheckinToken($token);

        if (!$event) {
            abort(404, 'Подію не знайдено');
        }

        $user = auth()->user();
        $person = $user?->person;

        return view('checkin.show', compact('event', 'person'));
    }

    /**
     * Process QR check-in
     */
    public function checkin(Request $request, string $token): JsonResponse
    {
        $event = Event::findByCheckinToken($token);

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Подію не знайдено',
            ], 404);
        }

        if (!$event->qr_checkin_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'QR check-in вимкнено для цієї події',
            ], 403);
        }

        $user = auth()->user();
        $person = $user?->person;

        if (!$person) {
            return response()->json([
                'success' => false,
                'message' => 'Профіль не знайдено. Увійдіть в систему.',
            ], 401);
        }

        // Verify person belongs to the same church as the event
        if ($person->church_id !== $event->church_id) {
            return response()->json([
                'success' => false,
                'message' => 'Ви не є членом цієї церкви',
            ], 403);
        }

        // Get or create attendance record for this event
        $attendance = Attendance::firstOrCreate(
            [
                'church_id' => $event->church_id,
                'attendable_type' => Event::class,
                'attendable_id' => $event->id,
                'date' => $event->date,
            ],
            [
                'type' => $event->is_service ? Attendance::TYPE_SERVICE : Attendance::TYPE_EVENT,
                'time' => $event->time,
                'recorded_by' => $user->id,
            ]
        );

        // Check if already checked in
        $existingRecord = $attendance->records()->where('person_id', $person->id)->first();

        if ($existingRecord && $existingRecord->present) {
            return response()->json([
                'success' => true,
                'already_checked_in' => true,
                'message' => 'Ви вже зареєстровані на цю подію',
                'checked_in_at' => $existingRecord->checked_in_at,
            ]);
        }

        // Mark as present
        $record = $attendance->markPresent($person);
        $attendance->recalculateCounts();

        return response()->json([
            'success' => true,
            'message' => 'Успішно зареєстровано!',
            'event' => [
                'title' => $event->title,
                'date' => $event->date->format('d.m.Y'),
                'time' => $event->time?->format('H:i'),
            ],
            'person' => [
                'name' => $person->full_name,
            ],
            'checked_in_at' => now()->format('H:i'),
        ]);
    }

    /**
     * Admin endpoint: Check in a person by scanning their QR or selecting from list
     */
    public function adminCheckin(Request $request): JsonResponse
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'person_id' => 'required|exists:people,id',
        ]);

        $event = Event::findOrFail($request->event_id);
        $person = Person::findOrFail($request->person_id);

        // Verify user has access to this church
        $user = auth()->user();
        if ($event->church_id !== $user->church_id || $person->church_id !== $user->church_id) {
            return response()->json([
                'success' => false,
                'message' => 'Доступ заборонено',
            ], 403);
        }

        // Get or create attendance
        $attendance = Attendance::firstOrCreate(
            [
                'church_id' => $event->church_id,
                'attendable_type' => Event::class,
                'attendable_id' => $event->id,
                'date' => $event->date,
            ],
            [
                'type' => $event->is_service ? Attendance::TYPE_SERVICE : Attendance::TYPE_EVENT,
                'time' => $event->time,
                'recorded_by' => $user->id,
            ]
        );

        // Mark as present
        $record = $attendance->markPresent($person);
        $attendance->recalculateCounts();

        return response()->json([
            'success' => true,
            'message' => "{$person->full_name} зареєстровано",
            'attendance_count' => $attendance->members_present,
        ]);
    }

    /**
     * Get today's events for the scanner
     */
    public function todayEvents(): JsonResponse
    {
        $user = auth()->user();

        $events = Event::where('church_id', $user->church_id)
            ->whereDate('date', today())
            ->with('attendance.records')
            ->orderBy('time')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'time' => $event->time?->format('H:i'),
                    'qr_checkin_enabled' => $event->qr_checkin_enabled,
                    'checkin_url' => $event->qr_checkin_enabled ? $event->checkin_url : null,
                    'attendance_count' => $event->attendance?->members_present ?? 0,
                ];
            });

        return response()->json([
            'events' => $events,
        ]);
    }

    /**
     * Generate/regenerate QR code for an event
     */
    public function generateQr(Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        $token = $event->generateCheckinToken();

        return response()->json([
            'success' => true,
            'token' => $token,
            'url' => $event->checkin_url,
        ]);
    }

    /**
     * Toggle QR check-in for an event
     */
    public function toggleQrCheckin(Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        $event->update([
            'qr_checkin_enabled' => !$event->qr_checkin_enabled,
        ]);

        // Generate token if enabling and no token exists
        if ($event->qr_checkin_enabled && !$event->checkin_token) {
            $event->generateCheckinToken();
        }

        return response()->json([
            'success' => true,
            'enabled' => $event->qr_checkin_enabled,
            'url' => $event->qr_checkin_enabled ? $event->checkin_url : null,
        ]);
    }
}
