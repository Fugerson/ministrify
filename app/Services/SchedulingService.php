<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\BlockoutDate;
use App\Models\Church;
use App\Models\Event;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\Position;
use App\Models\SchedulingConflict;
use App\Models\SchedulingPreference;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Unified Scheduling Service
 *
 * Combines functionality from:
 * - VolunteerSchedulingService (conflict detection, availability)
 * - RotationService (scoring, reports, batch assignment)
 * - AssignmentService (notifications)
 */
class SchedulingService
{
    protected ?Church $church = null;
    protected array $config;

    public function __construct(?Church $church = null)
    {
        $this->church = $church;
        $this->config = [
            'min_rest_days' => 7,
            'max_assignments_per_month' => 4,
            'weights' => [
                'balance' => 0.4,
                'skill' => 0.3,
                'availability' => 0.3,
            ],
        ];
    }

    /**
     * Set church context
     */
    public function forChurch(Church $church): self
    {
        $this->church = $church;
        return $this;
    }

    /**
     * Set configuration
     */
    public function setConfig(array $config): self
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    // =========================================================================
    // CONFLICT DETECTION
    // =========================================================================

    /**
     * Check all conflicts for a person on a specific event
     */
    public function checkConflicts(Person $person, Event $event, ?Position $position = null): array
    {
        $conflicts = [];

        // 1. Blockout dates
        if ($conflict = $this->checkBlockoutConflict($person, $event)) {
            $conflicts[] = $conflict;
        }

        // 2. Concurrent assignments
        if ($conflict = $this->checkConcurrentConflict($person, $event)) {
            $conflicts[] = $conflict;
        }

        // 3. Preference limits
        $conflicts = array_merge($conflicts, $this->checkPreferenceConflicts($person, $event, $position));

        // 4. Household preferences
        if ($conflict = $this->checkHouseholdConflict($person, $event)) {
            $conflicts[] = $conflict;
        }

        // 5. Already assigned to this event
        if ($this->isAlreadyAssigned($person, $event)) {
            $conflicts[] = [
                'type' => 'duplicate',
                'severity' => 'error',
                'details' => 'Вже призначений на цю подію',
            ];
        }

        return $conflicts;
    }

    /**
     * Check if person has a blockout on the event date
     */
    public function checkBlockoutConflict(Person $person, Event $event): ?array
    {
        $blockout = BlockoutDate::forPerson($person->id)
            ->active()
            ->forDate($event->date)
            ->forMinistry($event->ministry_id)
            ->first();

        if (!$blockout) {
            return null;
        }

        // Check time overlap if not all-day
        if (!$blockout->all_day && $event->time) {
            if (!$blockout->coversDateTime($event->date, $event->time)) {
                return null;
            }
        }

        return [
            'type' => 'blockout',
            'severity' => 'error',
            'details' => $blockout->reason_label . ($blockout->reason_note ? ': ' . $blockout->reason_note : ''),
            'blockout_id' => $blockout->id,
            'date_range' => $blockout->date_range,
        ];
    }

    /**
     * Check for concurrent assignment conflicts
     */
    public function checkConcurrentConflict(Person $person, Event $event): ?array
    {
        $concurrent = Assignment::where('person_id', $person->id)
            ->where('status', '!=', 'declined')
            ->whereHas('event', function ($q) use ($event) {
                $q->where('id', '!=', $event->id)
                  ->whereDate('date', $event->date);
            })
            ->with('event.ministry')
            ->first();

        if (!$concurrent) {
            return null;
        }

        $otherEvent = $concurrent->event;

        // Check time overlap
        if ($event->time && $otherEvent->time && !$this->eventsOverlap($event, $otherEvent)) {
            return null;
        }

        return [
            'type' => 'concurrent',
            'severity' => 'warning',
            'details' => "Вже призначений на «{$otherEvent->title}»" .
                        ($otherEvent->ministry ? " ({$otherEvent->ministry->name})" : '') .
                        ($otherEvent->time ? " о " . Carbon::parse($otherEvent->time)->format('H:i') : ''),
            'other_event_id' => $otherEvent->id,
            'other_assignment_id' => $concurrent->id,
        ];
    }

    /**
     * Check preference limit conflicts
     */
    public function checkPreferenceConflicts(Person $person, Event $event, ?Position $position = null): array
    {
        $conflicts = [];
        $preference = SchedulingPreference::where('person_id', $person->id)
            ->where('church_id', $event->church_id)
            ->first();

        if (!$preference) {
            return [];
        }

        $month = $event->date->month;
        $year = $event->date->year;

        // Count current assignments this month
        $monthCount = Assignment::where('person_id', $person->id)
            ->whereHas('event', fn($q) => $q
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->where('id', '!=', $event->id)
            )->count();

        // Global max limit
        if ($preference->max_times_per_month && $monthCount >= $preference->max_times_per_month) {
            $conflicts[] = [
                'type' => 'max_limit',
                'severity' => 'error',
                'details' => "Досягнуто максимум ({$preference->max_times_per_month} разів/місяць). Цього місяця: {$monthCount}",
                'current_count' => $monthCount,
                'max_count' => $preference->max_times_per_month,
            ];
        }

        // Preferred limit (warning only)
        if ($preference->preferred_times_per_month && $monthCount >= $preference->preferred_times_per_month) {
            $conflicts[] = [
                'type' => 'preference_limit',
                'severity' => 'warning',
                'details' => "Перевищує бажане ({$preference->preferred_times_per_month} разів/місяць). Цього місяця: {$monthCount}",
                'current_count' => $monthCount,
                'preferred_count' => $preference->preferred_times_per_month,
            ];
        }

        // Ministry-specific limits
        if ($event->ministry_id) {
            $ministryMax = $preference->getMaxForMinistry($event->ministry_id);
            if ($ministryMax) {
                $ministryCount = Assignment::where('person_id', $person->id)
                    ->whereHas('event', fn($q) => $q
                        ->where('ministry_id', $event->ministry_id)
                        ->whereMonth('date', $month)
                        ->whereYear('date', $year)
                        ->where('id', '!=', $event->id)
                    )->count();

                if ($ministryCount >= $ministryMax) {
                    $conflicts[] = [
                        'type' => 'max_limit',
                        'severity' => 'error',
                        'details' => "Максимум для служіння ({$ministryMax}/місяць). Цього місяця: {$ministryCount}",
                        'ministry_specific' => true,
                    ];
                }
            }
        }

        // Position-specific limits
        if ($position) {
            $positionMax = $preference->getMaxForPosition($position->id);
            if ($positionMax) {
                $positionCount = Assignment::where('person_id', $person->id)
                    ->where('position_id', $position->id)
                    ->whereHas('event', fn($q) => $q
                        ->whereMonth('date', $month)
                        ->whereYear('date', $year)
                        ->where('id', '!=', $event->id)
                    )->count();

                if ($positionCount >= $positionMax) {
                    $conflicts[] = [
                        'type' => 'max_limit',
                        'severity' => 'error',
                        'details' => "Максимум для позиції «{$position->name}» ({$positionMax}/місяць). Цього місяця: {$positionCount}",
                        'position_specific' => true,
                    ];
                }
            }
        }

        return $conflicts;
    }

    /**
     * Check household preference conflicts
     */
    public function checkHouseholdConflict(Person $person, Event $event): ?array
    {
        $preference = SchedulingPreference::where('person_id', $person->id)
            ->where('church_id', $event->church_id)
            ->first();

        if (!$preference || $preference->household_preference === 'none' || !$preference->prefer_with_person_id) {
            return null;
        }

        $partner = Person::find($preference->prefer_with_person_id);
        if (!$partner) {
            return null;
        }

        $partnerAssigned = Assignment::where('person_id', $partner->id)
            ->where('event_id', $event->id)
            ->exists();

        if ($preference->household_preference === 'together' && !$partnerAssigned) {
            return [
                'type' => 'household',
                'severity' => 'info',
                'details' => "Бажає служити разом з {$partner->full_name}, але партнер не призначений",
                'partner_id' => $partner->id,
            ];
        }

        if ($preference->household_preference === 'separate' && $partnerAssigned) {
            return [
                'type' => 'household',
                'severity' => 'warning',
                'details' => "Не бажає служити одночасно з {$partner->full_name}",
                'partner_id' => $partner->id,
            ];
        }

        return null;
    }

    /**
     * Check if person is already assigned to this event
     */
    protected function isAlreadyAssigned(Person $person, Event $event): bool
    {
        return Assignment::where('person_id', $person->id)
            ->where('event_id', $event->id)
            ->exists();
    }

    /**
     * Check if two events overlap in time
     */
    protected function eventsOverlap(Event $event1, Event $event2): bool
    {
        if (!$event1->time || !$event2->time) {
            return true; // Assume overlap if no times set
        }

        $duration1 = $event1->duration_minutes ?? 120;
        $duration2 = $event2->duration_minutes ?? 120;

        $start1 = Carbon::parse($event1->time);
        $end1 = $start1->copy()->addMinutes($duration1);
        $start2 = Carbon::parse($event2->time);
        $end2 = $start2->copy()->addMinutes($duration2);

        return $start1 < $end2 && $start2 < $end1;
    }

    // =========================================================================
    // AVAILABILITY & SCORING
    // =========================================================================

    /**
     * Get available volunteers sorted by recommendation score
     */
    public function getAvailableVolunteers(Event $event, ?Position $position = null): Collection
    {
        $ministry = $event->ministry;
        if (!$ministry) {
            return collect();
        }

        $volunteers = $ministry->members()
            ->with(['schedulingPreference', 'blockoutDates' => fn($q) => $q->active()->forDate($event->date)])
            ->get();

        return $volunteers->map(function ($person) use ($event, $position) {
            $conflicts = $this->checkConflicts($person, $event, $position);
            $hasErrors = collect($conflicts)->where('severity', 'error')->isNotEmpty();

            return [
                'person' => $person,
                'conflicts' => $conflicts,
                'has_errors' => $hasErrors,
                'has_warnings' => collect($conflicts)->where('severity', 'warning')->isNotEmpty(),
                'is_available' => !$hasErrors,
                'score' => $this->calculateScore($person, $event, $position),
            ];
        })->sortByDesc('score');
    }

    /**
     * Calculate assignment score (higher = better candidate)
     */
    public function calculateScore(Person $person, Event $event, ?Position $position = null): float
    {
        $balanceScore = $this->getBalanceScore($person, $event);
        $skillScore = $position ? $this->getSkillScore($person, $position) : 0.5;
        $availabilityScore = $this->getAvailabilityScore($person, $event);

        $score = ($balanceScore * $this->config['weights']['balance'])
               + ($skillScore * $this->config['weights']['skill'])
               + ($availabilityScore * $this->config['weights']['availability']);

        return round($score * 100, 1);
    }

    /**
     * Get balance score (lower recent assignments = higher score)
     */
    protected function getBalanceScore(Person $person, Event $event): float
    {
        $monthStart = $event->date->copy()->startOfMonth();
        $monthEnd = $event->date->copy()->endOfMonth();

        $monthlyCount = Assignment::where('person_id', $person->id)
            ->whereHas('event', fn($q) => $q->whereBetween('date', [$monthStart, $monthEnd]))
            ->count();

        if ($monthlyCount >= $this->config['max_assignments_per_month']) {
            return 0;
        }

        // Check rest days since last assignment
        $lastAssignment = Assignment::where('person_id', $person->id)
            ->whereHas('event', fn($q) => $q->where('date', '<', $event->date)->orderByDesc('date'))
            ->first();

        if ($lastAssignment && $lastAssignment->event) {
            $daysSince = $lastAssignment->event->date->diffInDays($event->date);
            if ($daysSince < $this->config['min_rest_days']) {
                return 0.2; // Too soon
            }
        } else {
            // Never assigned = best candidate
            return 1.0;
        }

        return max(0, min(1, 1 - ($monthlyCount / $this->config['max_assignments_per_month'])));
    }

    /**
     * Get skill score for position
     */
    protected function getSkillScore(Person $person, Position $position): float
    {
        $pivot = $person->positions()
            ->where('positions.id', $position->id)
            ->first();

        if (!$pivot) {
            return 0;
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
        $availability = $person->availability ?? [];

        if (!empty($availability)) {
            $dayName = strtolower($event->date->format('l'));
            if (isset($availability[$dayName]) && $availability[$dayName] === false) {
                return 0;
            }
        }

        // Check blockouts
        $hasBlockout = BlockoutDate::forPerson($person->id)
            ->active()
            ->forDate($event->date)
            ->exists();

        if ($hasBlockout) {
            return 0;
        }

        // Check if already serving that day
        $hasOtherAssignment = Assignment::where('person_id', $person->id)
            ->where('status', '!=', 'declined')
            ->whereHas('event', fn($q) => $q->whereDate('date', $event->date)->where('id', '!=', $event->id))
            ->exists();

        if ($hasOtherAssignment) {
            return 0.3;
        }

        return 1.0;
    }

    // =========================================================================
    // AUTO-SCHEDULING
    // =========================================================================

    /**
     * Auto-schedule volunteers for an event
     */
    public function autoSchedule(Event $event, array $positionIds = []): array
    {
        $results = [
            'assigned' => [],
            'failed' => [],
        ];

        $ministry = $event->ministry;
        if (!$ministry) {
            return $results;
        }

        // Get positions to fill
        if (empty($positionIds)) {
            $positionIds = $ministry->positions()
                ->where('is_active', true)
                ->whereDoesntHave('assignments', fn($q) => $q->where('event_id', $event->id))
                ->pluck('id')
                ->toArray();
        }

        foreach ($positionIds as $positionId) {
            $position = Position::find($positionId);
            if (!$position) continue;

            $available = $this->getAvailableVolunteers($event, $position)
                ->filter(fn($v) => $v['is_available'])
                ->values();

            if ($available->isEmpty()) {
                $results['failed'][] = [
                    'position' => $position,
                    'reason' => 'Немає доступних волонтерів',
                ];
                continue;
            }

            $best = $available->first();
            $person = $best['person'];

            $assignment = $this->assign($event, $position, $person, notify: false);

            // Log warnings as conflicts
            foreach ($best['conflicts'] as $conflict) {
                if ($conflict['severity'] !== 'error') {
                    SchedulingConflict::record($assignment->id, $conflict['type'], $conflict['details']);
                }
            }

            $this->updatePersonStats($person, $event);

            $results['assigned'][] = [
                'assignment' => $assignment,
                'person' => $person,
                'position' => $position,
                'score' => $best['score'],
                'warnings' => collect($best['conflicts'])->where('severity', 'warning')->values()->all(),
            ];
        }

        return $results;
    }

    /**
     * Auto-schedule for multiple upcoming events
     */
    public function autoScheduleUpcoming(Ministry $ministry, int $weeks = 4): array
    {
        $church = $this->church ?? $ministry->church;

        $events = Event::where('church_id', $church->id)
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
                'results' => $this->autoSchedule($event),
            ];
        }

        return $allResults;
    }

    // =========================================================================
    // ASSIGNMENT CRUD
    // =========================================================================

    /**
     * Create an assignment with optional notification
     */
    public function assign(Event $event, Position $position, Person $person, bool $notify = true): Assignment
    {
        $assignment = Assignment::create([
            'event_id' => $event->id,
            'position_id' => $position->id,
            'person_id' => $person->id,
            'status' => 'pending',
            'assigned_by' => auth()->id(),
        ]);

        if ($notify) {
            $this->notifyAssignment($assignment);
        }

        return $assignment;
    }

    /**
     * Send notification for a single assignment
     */
    public function notifyAssignment(Assignment $assignment): bool
    {
        $person = $assignment->person;
        $church = $person->church;

        if (!$person->telegram_chat_id || !$church->telegram_bot_token) {
            return false;
        }

        try {
            $telegram = new TelegramService($church->telegram_bot_token);
            $telegram->sendAssignmentNotification($assignment);
            $assignment->update(['notified_at' => now()]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Notify all pending assignments for an event
     */
    public function notifyAllForEvent(Event $event): int
    {
        $notified = 0;

        $assignments = $event->assignments()
            ->with(['person.church', 'event.ministry', 'position'])
            ->where('status', 'pending')
            ->whereNull('notified_at')
            ->get();

        foreach ($assignments as $assignment) {
            if ($this->notifyAssignment($assignment)) {
                $notified++;
            }
        }

        return $notified;
    }

    // =========================================================================
    // STATISTICS & REPORTS
    // =========================================================================

    /**
     * Update person's scheduling statistics
     */
    public function updatePersonStats(Person $person, Event $event): void
    {
        $person->update([
            'last_scheduled_at' => now(),
            'times_scheduled_this_month' => Assignment::where('person_id', $person->id)
                ->whereHas('event', fn($q) => $q
                    ->whereMonth('date', $event->date->month)
                    ->whereYear('date', $event->date->year)
                )->count(),
            'times_scheduled_this_year' => Assignment::where('person_id', $person->id)
                ->whereHas('event', fn($q) => $q->whereYear('date', $event->date->year))
                ->count(),
        ]);
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
            'pending' => $assignments->where('status', 'pending')->count(),
            'by_ministry' => $assignments->groupBy('event.ministry.name')
                ->map(fn($group) => $group->count())
                ->toArray(),
            'by_position' => $assignments->groupBy('position.name')
                ->map(fn($group) => $group->count())
                ->toArray(),
            'last_served' => $assignments
                ->whereIn('status', ['confirmed', 'completed'])
                ->sortByDesc('event.date')
                ->first()?->event?->date,
        ];
    }

    /**
     * Generate rotation report for a ministry
     */
    public function generateReport(Ministry $ministry, Carbon $startDate, Carbon $endDate): array
    {
        $church = $this->church ?? $ministry->church;

        $events = Event::where('church_id', $church->id)
            ->where('ministry_id', $ministry->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['assignments.person', 'assignments.position'])
            ->orderBy('date')
            ->get();

        $members = $ministry->members()->where('is_active', true)->get();

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
            'balance_score' => $this->calculateFairnessScore($memberStats),
        ];
    }

    /**
     * Calculate fairness score (0-100, higher = more balanced)
     */
    protected function calculateFairnessScore(array $memberStats): int
    {
        if (count($memberStats) < 2) {
            return 100;
        }

        $values = array_column($memberStats, 'assignments');
        $mean = array_sum($values) / count($values);

        if ($mean === 0) {
            return 100;
        }

        // Standard deviation
        $variance = array_reduce($values, fn($c, $v) => $c + pow($v - $mean, 2), 0) / count($values);
        $stdDev = sqrt($variance);

        // Coefficient of variation (lower = more balanced)
        $cv = $stdDev / $mean;

        return max(0, min(100, round((1 - $cv) * 100)));
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Format last scheduled date as human-readable
     */
    public function formatLastScheduled(?Carbon $date): string
    {
        if (!$date) {
            return 'Ніколи';
        }

        $weeks = $date->diffInWeeks(now());
        if ($weeks === 0) return 'Цього тижня';
        if ($weeks === 1) return '1 тиждень тому';
        if ($weeks < 4) return "{$weeks} тижні тому";

        $months = $date->diffInMonths(now());
        if ($months === 1) return '1 місяць тому';
        if ($months < 12) return "{$months} місяці тому";

        return $date->format('d.m.Y');
    }

    /**
     * Get conflict badge HTML
     */
    public function getConflictBadge(array $conflict): string
    {
        $config = SchedulingConflict::CONFLICT_TYPES[$conflict['type']] ?? null;
        if (!$config) return '';

        $colorClasses = match ($config['color']) {
            'red' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
            'orange' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400',
            'yellow' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
            'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        };

        return sprintf(
            '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium %s" title="%s">%s %s</span>',
            $colorClasses,
            htmlspecialchars($conflict['details']),
            $config['icon'],
            $config['label']
        );
    }

    /**
     * Get people grouped by availability status
     */
    public function getPeopleByAvailability(Event $event, Position $position): array
    {
        $volunteers = $this->getAvailableVolunteers($event, $position);

        return [
            'available' => $volunteers->filter(fn($v) => $v['is_available'] && !$v['has_warnings'])->values(),
            'warnings' => $volunteers->filter(fn($v) => $v['is_available'] && $v['has_warnings'])->values(),
            'unavailable' => $volunteers->filter(fn($v) => !$v['is_available'])->values(),
        ];
    }
}
