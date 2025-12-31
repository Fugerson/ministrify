<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Church;
use App\Models\Event;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\Position;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * @deprecated Use SchedulingService instead. This service will be removed in a future version.
 */
class RotationService
{
    protected Church $church;
    protected array $config;

    public function __construct(Church $church)
    {
        $this->church = $church;
        $this->config = [
            'min_rest_days' => 7,           // Minimum days between assignments
            'max_assignments_per_month' => 4, // Max times a volunteer can serve per month
            'balance_weight' => 0.4,         // Weight for balancing workload
            'skill_weight' => 0.3,           // Weight for skill match
            'availability_weight' => 0.3,    // Weight for availability
        ];
    }

    /**
     * Set rotation configuration
     */
    public function setConfig(array $config): self
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    /**
     * Auto-assign volunteers for an event
     */
    public function autoAssignEvent(Event $event): array
    {
        $results = [
            'assigned' => [],
            'unassigned' => [],
            'conflicts' => [],
        ];

        $ministry = $event->ministry;
        if (!$ministry) {
            return $results;
        }

        $positions = $ministry->positions()->get();

        foreach ($positions as $position) {
            // Check if position already has enough assignments
            $currentCount = $event->assignments()
                ->where('position_id', $position->id)
                ->count();

            $neededCount = $position->max_per_event ?? 1;

            if ($currentCount >= $neededCount) {
                continue;
            }

            // Find best candidates for this position
            $candidates = $this->getCandidatesForPosition($event, $position);

            for ($i = $currentCount; $i < $neededCount; $i++) {
                if ($candidates->isEmpty()) {
                    $results['unassigned'][] = [
                        'position' => $position->name,
                        'reason' => 'Немає доступних кандидатів',
                    ];
                    break;
                }

                $bestCandidate = $candidates->shift();

                // Check for conflicts
                $conflict = $this->checkConflicts($bestCandidate['person'], $event);
                if ($conflict) {
                    $results['conflicts'][] = [
                        'person' => $bestCandidate['person']->full_name,
                        'position' => $position->name,
                        'reason' => $conflict,
                    ];
                    continue;
                }

                // Create assignment
                $assignment = Assignment::create([
                    'event_id' => $event->id,
                    'person_id' => $bestCandidate['person']->id,
                    'position_id' => $position->id,
                    'status' => 'pending',
                    'assigned_by' => auth()->id(),
                ]);

                $results['assigned'][] = [
                    'person' => $bestCandidate['person']->full_name,
                    'position' => $position->name,
                    'score' => $bestCandidate['score'],
                ];
            }
        }

        return $results;
    }

    /**
     * Auto-assign for multiple upcoming events
     */
    public function autoAssignUpcoming(Ministry $ministry, int $weeks = 4): array
    {
        $events = Event::where('church_id', $this->church->id)
            ->where('ministry_id', $ministry->id)
            ->where('date', '>=', now())
            ->where('date', '<=', now()->addWeeks($weeks))
            ->orderBy('date')
            ->get();

        $allResults = [];

        foreach ($events as $event) {
            $allResults[$event->id] = [
                'event' => $event->title,
                'date' => $event->date->format('d.m.Y'),
                'results' => $this->autoAssignEvent($event),
            ];
        }

        return $allResults;
    }

    /**
     * Get ranked candidates for a position
     */
    protected function getCandidatesForPosition(Event $event, Position $position): Collection
    {
        $ministry = $event->ministry;

        // Get members who can serve in this position
        $members = $ministry->members()
            ->whereHas('positions', fn($q) => $q->where('positions.id', $position->id))
            ->get();

        return $members->map(function ($person) use ($event, $position) {
            return [
                'person' => $person,
                'score' => $this->calculateScore($person, $event, $position),
            ];
        })
        ->filter(fn($c) => $c['score'] > 0)
        ->sortByDesc('score')
        ->values();
    }

    /**
     * Calculate assignment score for a person
     */
    protected function calculateScore(Person $person, Event $event, Position $position): float
    {
        $score = 0;

        // 1. Balance score (lower recent assignments = higher score)
        $balanceScore = $this->getBalanceScore($person, $event);
        $score += $balanceScore * $this->config['balance_weight'];

        // 2. Skill match score
        $skillScore = $this->getSkillScore($person, $position);
        $score += $skillScore * $this->config['skill_weight'];

        // 3. Availability score
        $availabilityScore = $this->getAvailabilityScore($person, $event);
        $score += $availabilityScore * $this->config['availability_weight'];

        return round($score * 100);
    }

    /**
     * Get balance score based on recent assignments
     */
    protected function getBalanceScore(Person $person, Event $event): float
    {
        $monthStart = $event->date->copy()->startOfMonth();
        $monthEnd = $event->date->copy()->endOfMonth();

        // Count assignments this month
        $monthlyCount = Assignment::where('person_id', $person->id)
            ->whereHas('event', function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('date', [$monthStart, $monthEnd]);
            })
            ->count();

        if ($monthlyCount >= $this->config['max_assignments_per_month']) {
            return 0; // Already at max
        }

        // Check last assignment date
        $lastAssignment = Assignment::where('person_id', $person->id)
            ->whereHas('event', fn($q) => $q->where('date', '<', $event->date))
            ->whereHas('event', fn($q) => $q->orderByDesc('date'))
            ->first();

        if ($lastAssignment) {
            $daysSince = $lastAssignment->event->date->diffInDays($event->date);
            if ($daysSince < $this->config['min_rest_days']) {
                return 0.2; // Too soon, but not impossible
            }
        }

        // Higher score for less frequent volunteers
        $score = 1 - ($monthlyCount / $this->config['max_assignments_per_month']);
        return max(0, min(1, $score));
    }

    /**
     * Get skill score for position
     */
    protected function getSkillScore(Person $person, Position $position): float
    {
        // Check if person has this position's skill
        $hasPosition = $person->positions()
            ->where('positions.id', $position->id)
            ->exists();

        if (!$hasPosition) {
            return 0;
        }

        // Get experience level
        $pivot = $person->positions()
            ->where('positions.id', $position->id)
            ->first();

        if (!$pivot) {
            return 0.5;
        }

        $level = $pivot->pivot->skill_level ?? 'intermediate';

        return match($level) {
            'expert' => 1.0,
            'advanced' => 0.8,
            'intermediate' => 0.6,
            'beginner' => 0.4,
            default => 0.5,
        };
    }

    /**
     * Get availability score
     */
    protected function getAvailabilityScore(Person $person, Event $event): float
    {
        $dayOfWeek = $event->date->dayOfWeek;

        // Check person's availability settings
        $availability = $person->availability ?? [];

        if (empty($availability)) {
            return 0.7; // No preference set
        }

        $dayName = strtolower($event->date->format('l'));
        if (isset($availability[$dayName]) && $availability[$dayName] === false) {
            return 0; // Explicitly unavailable
        }

        // Check for specific date unavailability
        $unavailableDates = $person->unavailable_dates ?? [];
        if (in_array($event->date->format('Y-m-d'), $unavailableDates)) {
            return 0;
        }

        // Check for other events on the same day
        $hasOtherEvent = Assignment::where('person_id', $person->id)
            ->whereHas('event', fn($q) => $q->whereDate('date', $event->date))
            ->where('status', '!=', 'declined')
            ->exists();

        if ($hasOtherEvent) {
            return 0.3; // Already serving that day
        }

        return 1.0;
    }

    /**
     * Check for conflicts
     */
    protected function checkConflicts(Person $person, Event $event): ?string
    {
        // Already assigned to this event?
        $alreadyAssigned = Assignment::where('person_id', $person->id)
            ->where('event_id', $event->id)
            ->exists();

        if ($alreadyAssigned) {
            return 'Вже призначений на цю подію';
        }

        // Check for time conflicts
        $conflictingEvent = Event::where('church_id', $this->church->id)
            ->whereDate('date', $event->date)
            ->where('id', '!=', $event->id)
            ->whereHas('assignments', function ($q) use ($person) {
                $q->where('person_id', $person->id)
                  ->where('status', '!=', 'declined');
            })
            ->first();

        if ($conflictingEvent && $this->timesOverlap($event, $conflictingEvent)) {
            return "Конфлікт з подією: {$conflictingEvent->title}";
        }

        return null;
    }

    /**
     * Check if two events overlap in time
     */
    protected function timesOverlap(Event $event1, Event $event2): bool
    {
        if (!$event1->time || !$event2->time) {
            return true; // Assume overlap if no times set
        }

        $duration1 = $event1->duration_minutes ?? 120;
        $duration2 = $event2->duration_minutes ?? 120;

        $start1 = $event1->time;
        $end1 = $event1->time->copy()->addMinutes($duration1);
        $start2 = $event2->time;
        $end2 = $event2->time->copy()->addMinutes($duration2);

        return $start1 < $end2 && $start2 < $end1;
    }

    /**
     * Get volunteer statistics
     */
    public function getVolunteerStats(Person $person, ?Carbon $fromDate = null): array
    {
        $fromDate = $fromDate ?? now()->subMonths(3);

        $assignments = Assignment::where('person_id', $person->id)
            ->whereHas('event', fn($q) => $q->where('date', '>=', $fromDate))
            ->with(['event.ministry', 'position'])
            ->get();

        return [
            'total_assignments' => $assignments->count(),
            'confirmed' => $assignments->where('status', 'confirmed')->count(),
            'declined' => $assignments->where('status', 'declined')->count(),
            'by_ministry' => $assignments->groupBy('event.ministry.name')
                ->map(fn($group) => $group->count()),
            'by_position' => $assignments->groupBy('position.name')
                ->map(fn($group) => $group->count()),
            'last_served' => $assignments
                ->whereIn('status', ['confirmed', 'completed'])
                ->sortByDesc('event.date')
                ->first()?->event->date,
        ];
    }

    /**
     * Generate rotation report
     */
    public function generateReport(Ministry $ministry, Carbon $startDate, Carbon $endDate): array
    {
        $events = Event::where('church_id', $this->church->id)
            ->where('ministry_id', $ministry->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['assignments.person', 'assignments.position'])
            ->orderBy('date')
            ->get();

        $members = $ministry->members()->get();

        $memberStats = [];
        foreach ($members as $member) {
            $assignmentsCount = Assignment::where('person_id', $member->id)
                ->whereIn('event_id', $events->pluck('id'))
                ->whereIn('status', ['confirmed', 'completed', 'pending'])
                ->count();

            $memberStats[$member->id] = [
                'name' => $member->full_name,
                'assignments' => $assignmentsCount,
                'percentage' => $events->count() > 0
                    ? round(($assignmentsCount / $events->count()) * 100, 1)
                    : 0,
            ];
        }

        // Sort by assignments (least first for fairness analysis)
        uasort($memberStats, fn($a, $b) => $a['assignments'] <=> $b['assignments']);

        $totalAssignments = array_sum(array_column($memberStats, 'assignments'));
        $avgPerMember = $members->count() > 0
            ? round($totalAssignments / $members->count(), 1)
            : 0;

        return [
            'period' => $startDate->format('d.m.Y') . ' - ' . $endDate->format('d.m.Y'),
            'ministry' => $ministry->name,
            'total_events' => $events->count(),
            'total_assignments' => $totalAssignments,
            'average_per_member' => $avgPerMember,
            'member_stats' => $memberStats,
            'balance_score' => $this->calculateBalanceScore($memberStats),
        ];
    }

    /**
     * Calculate overall balance score (0-100, higher is more balanced)
     */
    protected function calculateBalanceScore(array $memberStats): int
    {
        if (count($memberStats) < 2) {
            return 100;
        }

        $values = array_column($memberStats, 'assignments');
        $mean = array_sum($values) / count($values);

        if ($mean === 0) {
            return 100;
        }

        // Calculate standard deviation
        $variance = array_reduce($values, function ($carry, $value) use ($mean) {
            return $carry + pow($value - $mean, 2);
        }, 0) / count($values);

        $stdDev = sqrt($variance);

        // Coefficient of variation (lower = more balanced)
        $cv = $stdDev / $mean;

        // Convert to score (0-100)
        return max(0, min(100, round((1 - $cv) * 100)));
    }
}
