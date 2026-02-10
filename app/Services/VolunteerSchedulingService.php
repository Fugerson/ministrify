<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\BlockoutDate;
use App\Models\Event;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\Position;
use App\Models\SchedulingConflict;
use App\Models\SchedulingPreference;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * @deprecated Use SchedulingService instead. This service will be removed in a future version.
 */
class VolunteerSchedulingService
{
    /**
     * Check all conflicts for a person on a specific event
     *
     * @return array Array of conflict objects with type, details, severity
     */
    public function checkConflicts(Person $person, Event $event, ?Position $position = null): array
    {
        $conflicts = [];

        // 1. Check blockouts
        $blockoutConflict = $this->checkBlockoutConflict($person, $event);
        if ($blockoutConflict) {
            $conflicts[] = $blockoutConflict;
        }

        // 2. Check concurrent assignments
        $concurrentConflict = $this->checkConcurrentConflict($person, $event);
        if ($concurrentConflict) {
            $conflicts[] = $concurrentConflict;
        }

        // 3. Check preference limits
        $preferenceConflicts = $this->checkPreferenceConflicts($person, $event, $position);
        $conflicts = array_merge($conflicts, $preferenceConflicts);

        // 4. Check household preferences
        $householdConflict = $this->checkHouseholdConflict($person, $event);
        if ($householdConflict) {
            $conflicts[] = $householdConflict;
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

        // Check time overlap if not all-day blockout
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
     * Check if person is already assigned to another event at the same time
     */
    public function checkConcurrentConflict(Person $person, Event $event): ?array
    {
        // Find other events at the same time
        $concurrentAssignment = Assignment::where('person_id', $person->id)
            ->whereHas('event', function ($q) use ($event) {
                $q->where('id', '!=', $event->id)
                  ->where('date', $event->date)
                  ->where(function ($timeQ) use ($event) {
                      // Same time or overlapping
                      if ($event->time && $event->end_time) {
                          $timeQ->where(function ($inner) use ($event) {
                              $inner->whereBetween('time', [$event->time, $event->end_time])
                                    ->orWhereBetween('end_time', [$event->time, $event->end_time])
                                    ->orWhere(function ($overlap) use ($event) {
                                        $overlap->where('time', '<=', $event->time)
                                                ->where('end_time', '>=', $event->end_time);
                                    });
                          });
                      } else {
                          // No time specified, any event on same day is potential conflict
                          $timeQ->whereNotNull('id');
                      }
                  });
            })
            ->with('event.ministry')
            ->first();

        if (!$concurrentAssignment) {
            return null;
        }

        $otherEvent = $concurrentAssignment->event;

        return [
            'type' => 'concurrent',
            'severity' => 'warning',
            'details' => "Вже призначений на «{$otherEvent->title}»" .
                        ($otherEvent->ministry ? " ({$otherEvent->ministry->name})" : '') .
                        ($otherEvent->time ? " о " . Carbon::parse($otherEvent->time)->format('H:i') : ''),
            'other_event_id' => $otherEvent->id,
            'other_assignment_id' => $concurrentAssignment->id,
        ];
    }

    /**
     * Check preference limits (max times per month, preferred frequency)
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

        $eventMonth = $event->date->month;
        $eventYear = $event->date->year;

        // Count current assignments this month
        $currentMonthCount = Assignment::where('person_id', $person->id)
            ->whereHas('event', function ($q) use ($eventMonth, $eventYear, $event) {
                $q->whereMonth('date', $eventMonth)
                  ->whereYear('date', $eventYear)
                  ->where('id', '!=', $event->id);
            })
            ->count();

        // Check global max limit
        if ($preference->max_times_per_month && $currentMonthCount >= $preference->max_times_per_month) {
            $conflicts[] = [
                'type' => 'max_limit',
                'severity' => 'error',
                'details' => "Досягнуто максимум ({$preference->max_times_per_month} разів/місяць). " .
                            "Цього місяця вже: {$currentMonthCount}",
                'current_count' => $currentMonthCount,
                'max_count' => $preference->max_times_per_month,
            ];
        }

        // Check preferred limit (warning only)
        if ($preference->preferred_times_per_month && $currentMonthCount >= $preference->preferred_times_per_month) {
            $conflicts[] = [
                'type' => 'preference_limit',
                'severity' => 'warning',
                'details' => "Перевищує бажане ({$preference->preferred_times_per_month} разів/місяць). " .
                            "Цього місяця: {$currentMonthCount}",
                'current_count' => $currentMonthCount,
                'preferred_count' => $preference->preferred_times_per_month,
            ];
        }

        // Check ministry-specific limits
        if ($event->ministry_id) {
            $ministryMax = $preference->getMaxForMinistry($event->ministry_id);
            if ($ministryMax) {
                $ministryCount = Assignment::where('person_id', $person->id)
                    ->whereHas('event', function ($q) use ($eventMonth, $eventYear, $event) {
                        $q->where('ministry_id', $event->ministry_id)
                          ->whereMonth('date', $eventMonth)
                          ->whereYear('date', $eventYear)
                          ->where('id', '!=', $event->id);
                    })
                    ->count();

                if ($ministryCount >= $ministryMax) {
                    $conflicts[] = [
                        'type' => 'max_limit',
                        'severity' => 'error',
                        'details' => "Максимум для цього служіння ({$ministryMax} разів/місяць). " .
                                    "Цього місяця: {$ministryCount}",
                        'current_count' => $ministryCount,
                        'max_count' => $ministryMax,
                        'ministry_specific' => true,
                    ];
                }
            }
        }

        // Check position-specific limits
        if ($position) {
            $positionMax = $preference->getMaxForPosition($position->id);
            if ($positionMax) {
                $positionCount = Assignment::where('person_id', $person->id)
                    ->where('position_id', $position->id)
                    ->whereHas('event', function ($q) use ($eventMonth, $eventYear, $event) {
                        $q->whereMonth('date', $eventMonth)
                          ->whereYear('date', $eventYear)
                          ->where('id', '!=', $event->id);
                    })
                    ->count();

                if ($positionCount >= $positionMax) {
                    $conflicts[] = [
                        'type' => 'max_limit',
                        'severity' => 'error',
                        'details' => "Максимум для позиції «{$position->name}» ({$positionMax} разів/місяць). " .
                                    "Цього місяця: {$positionCount}",
                        'current_count' => $positionCount,
                        'max_count' => $positionMax,
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

        // Check if partner is assigned to this event
        $partnerAssigned = Assignment::where('person_id', $partner->id)
            ->where('event_id', $event->id)
            ->exists();

        if ($preference->household_preference === 'together' && !$partnerAssigned) {
            return [
                'type' => 'household',
                'severity' => 'info',
                'details' => "Бажає служити разом з {$partner->full_name}, але партнер не призначений",
                'partner_id' => $partner->id,
                'partner_name' => $partner->full_name,
                'preference_type' => 'together',
            ];
        }

        if ($preference->household_preference === 'separate' && $partnerAssigned) {
            return [
                'type' => 'household',
                'severity' => 'warning',
                'details' => "Не бажає служити одночасно з {$partner->full_name} (догляд за дітьми)",
                'partner_id' => $partner->id,
                'partner_name' => $partner->full_name,
                'preference_type' => 'separate',
            ];
        }

        return null;
    }

    /**
     * Get available volunteers for an event/position, sorted by recommendation
     */
    public function getAvailableVolunteers(Event $event, ?Position $position = null): Collection
    {
        $ministry = $event->ministry;
        if (!$ministry) {
            return collect();
        }

        // Get all volunteers in the ministry
        $volunteers = $ministry->members()
            ->with(['schedulingPreference', 'blockoutDates' => function ($q) use ($event) {
                $q->active()->forDate($event->date);
            }])
            ->get();

        // Calculate availability and sort
        return $volunteers->map(function ($person) use ($event, $position) {
            $conflicts = $this->checkConflicts($person, $event, $position);

            return [
                'person' => $person,
                'conflicts' => $conflicts,
                'has_errors' => collect($conflicts)->where('severity', 'error')->isNotEmpty(),
                'has_warnings' => collect($conflicts)->where('severity', 'warning')->isNotEmpty(),
                'is_available' => empty($conflicts) || !collect($conflicts)->where('severity', 'error')->isNotEmpty(),
                'last_scheduled_at' => $person->last_scheduled_at,
                'times_this_month' => $person->times_scheduled_this_month,
                'sort_score' => $this->calculateSortScore($person, $conflicts),
            ];
        })->sortBy('sort_score');
    }

    /**
     * Calculate sort score for volunteer (lower = better candidate)
     */
    protected function calculateSortScore(Person $person, array $conflicts): int
    {
        $score = 0;

        // Heavy penalty for errors
        $errorCount = collect($conflicts)->where('severity', 'error')->count();
        $score += $errorCount * 1000;

        // Medium penalty for warnings
        $warningCount = collect($conflicts)->where('severity', 'warning')->count();
        $score += $warningCount * 100;

        // Prefer people who haven't served recently
        if ($person->last_scheduled_at) {
            $daysSinceLastScheduled = $person->last_scheduled_at->diffInDays(now());
            // Lower score for people who served long ago
            $score -= min($daysSinceLastScheduled, 60); // Cap at 60 days
        } else {
            // Never scheduled = very good candidate
            $score -= 100;
        }

        // Slight penalty for people who served many times this month
        $score += $person->times_scheduled_this_month * 10;

        return $score;
    }

    /**
     * Auto-schedule volunteers for an event
     */
    public function autoSchedule(Event $event, array $neededPositions = []): array
    {
        $results = [
            'assigned' => [],
            'failed' => [],
        ];

        if (empty($neededPositions)) {
            // Get positions from ministry that need filling
            $ministry = $event->ministry;
            if (!$ministry) {
                return $results;
            }

            $neededPositions = $ministry->positions()
                ->whereDoesntHave('assignments', fn($q) => $q->where('event_id', $event->id))
                ->pluck('id')
                ->toArray();
        }

        foreach ($neededPositions as $positionId) {
            $position = Position::find($positionId);
            if (!$position) {
                continue;
            }

            // Get available volunteers sorted by recommendation
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

            // Pick the best candidate
            $best = $available->first();
            $person = $best['person'];

            // Create assignment
            $assignment = Assignment::create([
                'event_id' => $event->id,
                'person_id' => $person->id,
                'position_id' => $position->id,
                'status' => 'pending',
            ]);

            // Log any warnings as conflicts (for tracking)
            foreach ($best['conflicts'] as $conflict) {
                if ($conflict['severity'] !== 'error') {
                    SchedulingConflict::record($assignment->id, $conflict['type'], $conflict['details']);
                }
            }

            // Update person's scheduling stats
            $this->updatePersonStats($person, $event);

            $results['assigned'][] = [
                'assignment' => $assignment,
                'person' => $person,
                'position' => $position,
                'warnings' => collect($best['conflicts'])->where('severity', 'warning')->values()->all(),
            ];
        }

        return $results;
    }

    /**
     * Update person's scheduling statistics
     */
    public function updatePersonStats(Person $person, Event $event): void
    {
        $person->update([
            'last_scheduled_at' => now(),
            'times_scheduled_this_month' => Assignment::where('person_id', $person->id)
                ->whereHas('event', fn($q) => $q->whereMonth('date', $event->date->month)->whereYear('date', $event->date->year))
                ->count(),
            'times_scheduled_this_year' => Assignment::where('person_id', $person->id)
                ->whereHas('event', fn($q) => $q->whereYear('date', $event->date->year))
                ->count(),
        ]);
    }

    /**
     * Format "last scheduled" as human-readable
     */
    public function formatLastScheduled(?Carbon $date): string
    {
        if (!$date) {
            return 'Ніколи';
        }

        $weeks = $date->diffInWeeks(now());
        if ($weeks === 0) {
            return 'Цього тижня';
        }
        if ($weeks === 1) {
            return '1 тиждень тому';
        }
        if ($weeks < 4) {
            return "{$weeks} тижні тому";
        }

        $months = $date->diffInMonths(now());
        if ($months === 1) {
            return '1 місяць тому';
        }
        if ($months < 12) {
            return "{$months} місяці тому";
        }

        return $date->format('d.m.Y');
    }

    /**
     * Get conflict badge HTML for display
     */
    public function getConflictBadge(array $conflict): string
    {
        $config = SchedulingConflict::CONFLICT_TYPES[$conflict['type']] ?? null;
        if (!$config) {
            return '';
        }

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
}
