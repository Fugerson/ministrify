<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateRecurringEvents extends Command
{
    protected $signature = 'app:generate-recurring-events {--weeks=4 : Number of weeks to generate}';
    protected $description = 'Generate recurring events for the specified number of weeks';

    public function handle(): int
    {
        $weeks = (int) $this->option('weeks');
        $this->info("Generating recurring events for the next {$weeks} weeks");

        $recurringEvents = Event::whereNotNull('recurrence_rule')
            ->whereNull('parent_event_id')
            ->with('ministry')
            ->get();

        $created = 0;

        foreach ($recurringEvents as $event) {
            $rule = $event->recurrence_rule;

            if (!$rule || !isset($rule['frequency'])) {
                continue;
            }

            $this->line("Processing: {$event->title} ({$event->ministry->name})");

            $dates = $this->calculateDates($event, $weeks);

            foreach ($dates as $date) {
                $exists = Event::where('parent_event_id', $event->id)
                    ->whereDate('date', $date)
                    ->exists();

                if (!$exists) {
                    Event::create([
                        'church_id' => $event->church_id,
                        'ministry_id' => $event->ministry_id,
                        'title' => $event->title,
                        'date' => $date,
                        'time' => $event->time,
                        'notes' => $event->notes,
                        'parent_event_id' => $event->id,
                    ]);

                    $created++;
                    $this->line("  Created event for {$date->format('d.m.Y')}");
                }
            }
        }

        $this->info("Done! Created {$created} events.");

        return self::SUCCESS;
    }

    protected function calculateDates(Event $event, int $weeks): array
    {
        $dates = [];
        $rule = $event->recurrence_rule;
        $frequency = $rule['frequency'] ?? 'weekly';
        $days = $rule['days'] ?? [];
        $startDate = now()->startOfDay();
        $endDate = now()->addWeeks($weeks)->endOfDay();

        if ($frequency === 'weekly' && !empty($days)) {
            $dayMap = [
                'sun' => Carbon::SUNDAY,
                'mon' => Carbon::MONDAY,
                'tue' => Carbon::TUESDAY,
                'wed' => Carbon::WEDNESDAY,
                'thu' => Carbon::THURSDAY,
                'fri' => Carbon::FRIDAY,
                'sat' => Carbon::SATURDAY,
            ];

            foreach ($days as $day) {
                $dayNumber = $dayMap[strtolower($day)] ?? null;
                if ($dayNumber === null) continue;

                $current = $startDate->copy();
                while ($current->dayOfWeek !== $dayNumber) {
                    $current->addDay();
                }

                while ($current <= $endDate) {
                    if ($current > now()) {
                        $dates[] = $current->copy();
                    }
                    $current->addWeek();
                }
            }
        } elseif ($frequency === 'daily') {
            $current = $startDate->copy()->addDay();
            while ($current <= $endDate) {
                $dates[] = $current->copy();
                $current->addDay();
            }
        } elseif ($frequency === 'monthly') {
            $dayOfMonth = $rule['day_of_month'] ?? $event->date->day;
            $current = $startDate->copy()->day($dayOfMonth);

            if ($current < now()) {
                $current->addMonth();
            }

            while ($current <= $endDate) {
                $dates[] = $current->copy();
                $current->addMonth();
            }
        }

        return $dates;
    }
}
