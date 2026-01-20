<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ministry;
use App\Models\Person;
use App\Services\RotationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RotationController extends Controller
{
    /**
     * Show rotation dashboard
     */
    public function index()
    {
        $church = $this->getCurrentChurch();

        $ministries = $church->ministries()
            ->withCount(['members', 'positions'])
            ->get();

        $upcomingEvents = Event::where('church_id', $church->id)
            ->where('date', '>=', now())
            ->where('date', '<=', now()->addWeeks(4))
            ->with(['ministry.positions', 'assignments.person', 'assignments.position'])
            ->orderBy('date')
            ->get()
            ->groupBy('ministry_id');

        return view('rotation.index', compact('ministries', 'upcomingEvents'));
    }

    /**
     * Show rotation settings for a ministry
     */
    public function ministry(Ministry $ministry)
    {
        Gate::authorize('view-ministry', $ministry);

        $church = $this->getCurrentChurch();

        // Get upcoming events
        $events = Event::where('church_id', $church->id)
            ->where('ministry_id', $ministry->id)
            ->where('date', '>=', now())
            ->where('date', '<=', now()->addWeeks(4))
            ->with(['assignments.person', 'assignments.position'])
            ->orderBy('date')
            ->get();

        // Get members (positions are stored in pivot table)
        $members = $ministry->members()->get();

        // Get all positions for this ministry for display
        $positions = $ministry->positions()->get()->keyBy('id');

        // Get rotation report for last 3 months
        $rotationService = new RotationService($church);
        $report = $rotationService->generateReport(
            $ministry,
            now()->subMonths(3),
            now()
        );

        return view('rotation.ministry', compact('ministry', 'events', 'members', 'positions', 'report'));
    }

    /**
     * Auto-assign a single event
     */
    public function autoAssignEvent(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $church = $this->getCurrentChurch();
        $rotationService = new RotationService($church);

        // Apply custom config if provided
        if ($request->has('config')) {
            $rotationService->setConfig($request->config);
        }

        $results = $rotationService->autoAssignEvent($event);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'results' => $results,
                'message' => count($results['assigned']) . ' призначень створено',
            ]);
        }

        $message = count($results['assigned']) . ' волонтерів призначено.';
        if (count($results['unassigned']) > 0) {
            $message .= ' ' . count($results['unassigned']) . ' позицій залишилось без призначення.';
        }

        return back()->with('success', $message);
    }

    /**
     * Auto-assign multiple upcoming events
     */
    public function autoAssignBulk(Request $request, Ministry $ministry)
    {
        Gate::authorize('manage-ministry', $ministry);

        $request->validate([
            'weeks' => 'integer|min:1|max:12',
        ]);

        $church = $this->getCurrentChurch();
        $rotationService = new RotationService($church);

        $weeks = $request->input('weeks', 4);
        $results = $rotationService->autoAssignUpcoming($ministry, $weeks);

        $totalAssigned = 0;
        $totalUnassigned = 0;

        foreach ($results as $eventResult) {
            $totalAssigned += count($eventResult['results']['assigned']);
            $totalUnassigned += count($eventResult['results']['unassigned']);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'results' => $results,
                'summary' => [
                    'events' => count($results),
                    'assigned' => $totalAssigned,
                    'unassigned' => $totalUnassigned,
                ],
            ]);
        }

        return back()->with('success', "Розподіл завершено: {$totalAssigned} призначень для " . count($results) . " подій.");
    }

    /**
     * Get volunteer statistics
     */
    public function volunteerStats(Request $request, Person $person)
    {
        $church = $this->getCurrentChurch();
        $rotationService = new RotationService($church);

        $fromDate = $request->has('from')
            ? Carbon::parse($request->from)
            : now()->subMonths(3);

        $stats = $rotationService->getVolunteerStats($person, $fromDate);

        if ($request->ajax()) {
            return response()->json($stats);
        }

        return view('rotation.volunteer-stats', compact('person', 'stats'));
    }

    /**
     * Generate rotation report
     */
    public function report(Request $request, Ministry $ministry)
    {
        Gate::authorize('view-ministry', $ministry);

        $church = $this->getCurrentChurch();
        $rotationService = new RotationService($church);

        $startDate = $request->has('start')
            ? Carbon::parse($request->start)
            : now()->subMonths(3);

        $endDate = $request->has('end')
            ? Carbon::parse($request->end)
            : now();

        $report = $rotationService->generateReport($ministry, $startDate, $endDate);

        if ($request->ajax()) {
            return response()->json($report);
        }

        return view('rotation.report', compact('ministry', 'report', 'startDate', 'endDate'));
    }

    /**
     * Preview auto-assignment without saving
     */
    public function previewAutoAssign(Request $request, Event $event)
    {
        $this->authorize('view', $event);

        $church = $this->getCurrentChurch();
        $rotationService = new RotationService($church);

        // Get candidates for each position
        $ministry = $event->ministry;
        $positions = $ministry->positions()->get();

        $preview = [];

        foreach ($positions as $position) {
            $currentCount = $event->assignments()
                ->where('position_id', $position->id)
                ->count();

            $neededCount = $position->max_per_event ?? 1;

            if ($currentCount >= $neededCount) {
                $preview[] = [
                    'position' => $position->name,
                    'status' => 'filled',
                    'current' => $currentCount,
                    'needed' => $neededCount,
                    'candidates' => [],
                ];
                continue;
            }

            // Get top 5 candidates using reflection to access protected method
            $reflection = new \ReflectionMethod($rotationService, 'getCandidatesForPosition');
            $reflection->setAccessible(true);
            $candidates = $reflection->invoke($rotationService, $event, $position);

            $preview[] = [
                'position' => $position->name,
                'status' => 'open',
                'current' => $currentCount,
                'needed' => $neededCount,
                'candidates' => $candidates->take(5)->map(fn($c) => [
                    'id' => $c['person']->id,
                    'name' => $c['person']->full_name,
                    'score' => $c['score'],
                ])->values()->toArray(),
            ];
        }

        return response()->json([
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'date' => $event->date->format('d.m.Y'),
            ],
            'positions' => $preview,
        ]);
    }
}
