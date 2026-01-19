<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Models\Event;
use App\Services\CalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Get calendar feed in iCal format (for subscription)
     */
    public function feed(Request $request, CalendarService $calendarService)
    {
        $token = $request->get('token');

        if (!$token) {
            abort(401, 'Token required');
        }

        $church = Church::where('calendar_token', $token)->first();

        if (!$church) {
            abort(404, 'Calendar not found');
        }

        $ministryId = $request->get('ministry');

        $icalContent = $calendarService->exportToIcal($church, $ministryId);

        return response($icalContent)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'inline; filename="calendar.ics"')
            ->header('Cache-Control', 'no-cache, must-revalidate')
            ->header('Pragma', 'no-cache');
    }

    /**
     * Get events as JSON
     */
    public function events(Request $request)
    {
        $token = $request->get('token');

        if (!$token) {
            return response()->json(['error' => 'Token required'], 401);
        }

        $church = Church::where('calendar_token', $token)->first();

        if (!$church) {
            return response()->json(['error' => 'Calendar not found'], 404);
        }

        $query = Event::where('church_id', $church->id)
            ->with(['ministry:id,name,color']);

        // Filter by ministry
        if ($ministryId = $request->get('ministry')) {
            $query->where('ministry_id', $ministryId);
        }

        // Date range
        if ($startDate = $request->get('start')) {
            $query->where('date', '>=', Carbon::parse($startDate));
        }

        if ($endDate = $request->get('end')) {
            $query->where('date', '<=', Carbon::parse($endDate));
        }

        // Default: upcoming events
        if (!$request->get('start') && !$request->get('end')) {
            $query->where('date', '>=', now()->startOfDay());
        }

        $events = $query->orderBy('date')->orderBy('time')->get();

        return response()->json([
            'church' => [
                'name' => $church->name,
                'slug' => $church->slug,
            ],
            'events' => $events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'date' => $event->date->format('Y-m-d'),
                    'time' => $event->time?->format('H:i'),
                    'datetime' => $event->date->format('Y-m-d') . ($event->time ? 'T' . $event->time->format('H:i:s') : ''),
                    'location' => $event->location,
                    'notes' => $event->notes,
                    'is_public' => $event->is_public,
                    'ministry' => $event->ministry ? [
                        'id' => $event->ministry->id,
                        'name' => $event->ministry->name,
                        'color' => $event->ministry->color,
                    ] : null,
                ];
            }),
            'count' => $events->count(),
        ]);
    }

    /**
     * Get single event
     */
    public function event(Request $request, int $id)
    {
        $token = $request->get('token');

        if (!$token) {
            return response()->json(['error' => 'Token required'], 401);
        }

        $church = Church::where('calendar_token', $token)->first();

        if (!$church) {
            return response()->json(['error' => 'Calendar not found'], 404);
        }

        $event = Event::where('church_id', $church->id)
            ->where('id', $id)
            ->with(['ministry:id,name,color'])
            ->first();

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        return response()->json([
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'date' => $event->date->format('Y-m-d'),
                'time' => $event->time?->format('H:i'),
                'datetime' => $event->date->format('Y-m-d') . ($event->time ? 'T' . $event->time->format('H:i:s') : ''),
                'location' => $event->location,
                'notes' => $event->notes,
                'public_description' => $event->public_description,
                'is_public' => $event->is_public,
                'allow_registration' => $event->allow_registration,
                'registration_limit' => $event->registration_limit,
                'remaining_spaces' => $event->remaining_spaces,
                'ministry' => $event->ministry ? [
                    'id' => $event->ministry->id,
                    'name' => $event->ministry->name,
                    'color' => $event->ministry->color,
                ] : null,
            ],
        ]);
    }

    /**
     * Get ministries list
     */
    public function ministries(Request $request)
    {
        $token = $request->get('token');

        if (!$token) {
            return response()->json(['error' => 'Token required'], 401);
        }

        $church = Church::where('calendar_token', $token)->first();

        if (!$church) {
            return response()->json(['error' => 'Calendar not found'], 404);
        }

        $ministries = $church->ministries()
            ->select('id', 'name', 'color')
            ->get();

        return response()->json([
            'ministries' => $ministries,
        ]);
    }
}
