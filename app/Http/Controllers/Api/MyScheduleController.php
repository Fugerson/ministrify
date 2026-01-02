<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MyScheduleController extends Controller
{
    /**
     * Get user's upcoming responsibilities for PWA offline caching
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->person) {
            return response()->json([
                'error' => 'No profile found',
                'responsibilities' => [],
            ], 200);
        }

        $responsibilities = $user->person->responsibilities()
            ->with(['event.ministry:id,name,color'])
            ->whereHas('event', fn($q) => $q->where('date', '>=', now()->startOfDay()))
            ->get()
            ->sortBy(fn($r) => $r->event->date)
            ->values();

        return response()->json([
            'responsibilities' => $responsibilities->map(function ($r) {
                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'status' => $r->status,
                    'status_label' => $r->status_label,
                    'event' => [
                        'id' => $r->event->id,
                        'title' => $r->event->title,
                        'date' => $r->event->date->format('Y-m-d'),
                        'date_formatted' => $r->event->date->format('d'),
                        'month' => $r->event->date->translatedFormat('M'),
                        'time' => $r->event->time?->format('H:i'),
                        'ministry' => $r->event->ministry ? [
                            'id' => $r->event->ministry->id,
                            'name' => $r->event->ministry->name,
                            'color' => $r->event->ministry->color,
                        ] : null,
                    ],
                ];
            }),
            'synced_at' => now()->toIso8601String(),
            'count' => $responsibilities->count(),
        ]);
    }

    /**
     * Confirm a responsibility (for offline sync)
     */
    public function confirm(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->person) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $responsibility = $user->person->responsibilities()->find($id);

        if (!$responsibility) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $responsibility->update(['status' => 'confirmed']);

        return response()->json([
            'success' => true,
            'responsibility' => [
                'id' => $responsibility->id,
                'status' => 'confirmed',
                'status_label' => $responsibility->status_label,
            ],
        ]);
    }

    /**
     * Decline a responsibility (for offline sync)
     */
    public function decline(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->person) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $responsibility = $user->person->responsibilities()->find($id);

        if (!$responsibility) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $responsibility->update(['status' => 'declined']);

        return response()->json([
            'success' => true,
            'responsibility' => [
                'id' => $responsibility->id,
                'status' => 'declined',
                'status_label' => $responsibility->status_label,
            ],
        ]);
    }
}
