<?php

namespace App\Services;

use App\Models\Board;
use App\Models\BoardCard;
use App\Models\Person;

class VisitorFollowupService
{
    /**
     * Create follow-up tasks when a new guest is added
     */
    public function createFollowupTasks(Person $person): void
    {
        if ($person->membership_status !== Person::STATUS_GUEST) {
            return;
        }

        $church = $person->church;
        if (!$church) {
            return;
        }

        // Find or create the task tracker board
        $board = Board::where('church_id', $church->id)
            ->where('name', 'Трекер завдань')
            ->first();

        if (!$board) {
            return;
        }

        // Find the first column (usually "To Do" or similar)
        $column = $board->columns()->orderBy('position')->first();
        if (!$column) {
            return;
        }

        // Create follow-up task
        BoardCard::create([
            'column_id' => $column->id,
            'title' => "Зв'язатися з гостем: {$person->full_name}",
            'description' => $this->buildTaskDescription($person),
            'priority' => 'high',
            'due_date' => now()->addDays(3),
            'position' => 0,
            'labels' => ['follow-up', 'guest'],
        ]);

        // Create welcome call task if phone exists
        if ($person->phone) {
            BoardCard::create([
                'column_id' => $column->id,
                'title' => "Привітальний дзвінок: {$person->full_name}",
                'description' => "Зателефонувати гостю та привітати з відвідуванням церкви.\n\nТелефон: {$person->phone}",
                'priority' => 'medium',
                'due_date' => now()->addDays(1),
                'position' => 1,
                'labels' => ['follow-up', 'call'],
            ]);
        }
    }

    /**
     * Build task description with person info
     */
    private function buildTaskDescription(Person $person): string
    {
        $lines = [
            "## Інформація про гостя",
            "",
            "**Ім'я:** {$person->full_name}",
        ];

        if ($person->phone) {
            $lines[] = "**Телефон:** {$person->phone}";
        }

        if ($person->email) {
            $lines[] = "**Email:** {$person->email}";
        }

        if ($person->first_visit_date) {
            $lines[] = "**Перший візит:** " . $person->first_visit_date->format('d.m.Y');
        }

        $lines[] = "";
        $lines[] = "## Завдання";
        $lines[] = "- [ ] Зв'язатися протягом 3 днів";
        $lines[] = "- [ ] Дізнатися про враження від візиту";
        $lines[] = "- [ ] Запросити на наступне богослужіння";
        $lines[] = "- [ ] Додати до групи новоприбулих (якщо є)";

        return implode("\n", $lines);
    }

    /**
     * Check for guests needing follow-up and create reminders
     */
    public function checkPendingFollowups(int $churchId): array
    {
        $threeDaysAgo = now()->subDays(3);
        $oneWeekAgo = now()->subWeeks(1);

        // Find guests added more than 3 days ago who haven't been contacted
        $needsFollowup = Person::where('church_id', $churchId)
            ->where('membership_status', Person::STATUS_GUEST)
            ->where('created_at', '<=', $threeDaysAgo)
            ->whereNull('last_contact_date')
            ->get();

        // Find guests who visited once but haven't returned
        $noReturn = Person::where('church_id', $churchId)
            ->where('membership_status', Person::STATUS_GUEST)
            ->where('created_at', '<=', $oneWeekAgo)
            ->whereDoesntHave('attendanceRecords', function ($q) use ($oneWeekAgo) {
                $q->where('created_at', '>=', $oneWeekAgo);
            })
            ->get();

        return [
            'needs_followup' => $needsFollowup,
            'no_return' => $noReturn,
        ];
    }

    /**
     * Update person status from guest to newcomer
     */
    public function promoteToNewcomer(Person $person): void
    {
        if ($person->membership_status === Person::STATUS_GUEST) {
            $person->update([
                'membership_status' => Person::STATUS_NEWCOMER,
            ]);
        }
    }

    /**
     * Get visitor statistics for a church
     */
    public function getVisitorStats(int $churchId): array
    {
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        return [
            'guests_this_month' => Person::where('church_id', $churchId)
                ->where('membership_status', Person::STATUS_GUEST)
                ->where('created_at', '>=', $thisMonth)
                ->count(),
            'guests_last_month' => Person::where('church_id', $churchId)
                ->where('membership_status', Person::STATUS_GUEST)
                ->whereBetween('created_at', [$lastMonth, $thisMonth])
                ->count(),
            'total_guests' => Person::where('church_id', $churchId)
                ->where('membership_status', Person::STATUS_GUEST)
                ->count(),
            'converted_this_month' => Person::where('church_id', $churchId)
                ->where('membership_status', '!=', Person::STATUS_GUEST)
                ->whereNotNull('first_visit_date')
                ->where('updated_at', '>=', $thisMonth)
                ->count(),
            'conversion_rate' => $this->calculateConversionRate($churchId),
        ];
    }

    /**
     * Calculate conversion rate from guest to member
     */
    private function calculateConversionRate(int $churchId): float
    {
        $totalGuests = Person::where('church_id', $churchId)
            ->whereNotNull('first_visit_date')
            ->count();

        if ($totalGuests === 0) {
            return 0;
        }

        $converted = Person::where('church_id', $churchId)
            ->whereNotNull('first_visit_date')
            ->whereIn('membership_status', [Person::STATUS_MEMBER, Person::STATUS_ACTIVE])
            ->count();

        return round(($converted / $totalGuests) * 100, 1);
    }
}
