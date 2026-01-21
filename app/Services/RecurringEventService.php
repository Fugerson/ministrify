<?php

namespace App\Services;

use App\Models\Event;
use Carbon\Carbon;

class RecurringEventService
{
    /**
     * Available recurrence rules
     */
    public const RULES = [
        'daily' => 'Щодня',
        'weekly' => 'Щотижня',
        'biweekly' => 'Раз на 2 тижні',
        'monthly' => 'Щомісяця',
        'yearly' => 'Щороку',
        'weekdays' => 'Щоденно (будні)',
        'custom' => 'Власний інтервал',
    ];

    /**
     * Generate recurring events based on a parent event
     */
    public function generateRecurringEvents(
        Event $parentEvent,
        string $rule,
        string $endType = 'count',
        int $endCount = 12,
        ?string $endDate = null
    ): array {
        $dates = $this->calculateRecurringDates(
            $parentEvent->date,
            $rule,
            $endType,
            $endCount,
            $endDate
        );

        $createdEvents = [];

        foreach ($dates as $date) {
            $createdEvents[] = Event::create([
                'church_id' => $parentEvent->church_id,
                'ministry_id' => $parentEvent->ministry_id,
                'title' => $parentEvent->title,
                'date' => $date,
                'time' => $parentEvent->time,
                'notes' => $parentEvent->notes,
                'is_service' => $parentEvent->is_service,
                'track_attendance' => $parentEvent->track_attendance,
                'parent_event_id' => $parentEvent->id,
            ]);
        }

        return $createdEvents;
    }

    /**
     * Calculate recurring dates based on rule
     */
    public function calculateRecurringDates(
        Carbon $startDate,
        string $rule,
        string $endType,
        int $endCount,
        ?string $endDate
    ): array {
        $dates = [];
        $currentDate = $startDate->copy();
        $maxDate = $endType === 'date' && $endDate ? Carbon::parse($endDate) : null;
        $count = 0;
        $maxIterations = $endType === 'count' ? $endCount - 1 : 365;

        // Parse custom rule
        $interval = 1;
        $frequency = 'week';
        if (str_starts_with($rule, 'custom:')) {
            $parts = explode(':', $rule);
            $interval = (int) ($parts[1] ?? 1);
            $frequency = $parts[2] ?? 'week';
            $rule = 'custom';
        }

        while ($count < $maxIterations) {
            $currentDate = $this->advanceDate($currentDate, $rule, $interval, $frequency);

            if ($currentDate === null) {
                break;
            }

            // Check if we've passed the end date
            if ($maxDate && $currentDate->gt($maxDate)) {
                break;
            }

            $dates[] = $currentDate->copy();
            $count++;
        }

        return $dates;
    }

    /**
     * Advance date based on recurrence rule
     */
    private function advanceDate(Carbon $date, string $rule, int $interval, string $frequency): ?Carbon
    {
        switch ($rule) {
            case 'daily':
                return $date->addDay();

            case 'weekly':
                return $date->addWeek();

            case 'biweekly':
                return $date->addWeeks(2);

            case 'monthly':
                return $date->addMonth();

            case 'yearly':
                return $date->addYear();

            case 'weekdays':
                do {
                    $date->addDay();
                } while ($date->isWeekend());
                return $date;

            case 'custom':
                return match ($frequency) {
                    'day' => $date->addDays($interval),
                    'week' => $date->addWeeks($interval),
                    'month' => $date->addMonths($interval),
                    'year' => $date->addYears($interval),
                    default => $date->addWeeks($interval),
                };

            default:
                return null;
        }
    }

    /**
     * Delete all future recurring events for a parent event
     */
    public function deleteFutureRecurringEvents(Event $parentEvent): int
    {
        return Event::where('parent_event_id', $parentEvent->id)
            ->where('date', '>', now())
            ->delete();
    }

    /**
     * Update all future recurring events
     */
    public function updateFutureRecurringEvents(Event $parentEvent, array $data): int
    {
        $allowedFields = ['title', 'time', 'notes', 'is_service', 'track_attendance'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));

        return Event::where('parent_event_id', $parentEvent->id)
            ->where('date', '>', now())
            ->update($updateData);
    }

    /**
     * Get all events in a recurring series
     */
    public function getRecurringSeries(Event $event): \Illuminate\Database\Eloquent\Collection
    {
        $parentId = $event->parent_event_id ?? $event->id;

        return Event::where(function ($query) use ($parentId, $event) {
            $query->where('id', $parentId)
                ->orWhere('parent_event_id', $parentId);
        })
            ->orderBy('date')
            ->get();
    }
}
