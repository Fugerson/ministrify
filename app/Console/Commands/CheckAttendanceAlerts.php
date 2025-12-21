<?php

namespace App\Console\Commands;

use App\Models\Church;
use App\Models\Person;
use App\Models\AttendanceRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckAttendanceAlerts extends Command
{
    protected $signature = 'app:check-attendance-alerts {--weeks=3 : Number of weeks without attendance}';
    protected $description = 'Check for people who have not attended for X weeks';

    public function handle(): int
    {
        $weeks = (int) $this->option('weeks');
        $cutoffDate = now()->subWeeks($weeks);

        $this->info("Checking for people who haven't attended since {$cutoffDate->format('Y-m-d')}");

        $churches = Church::all();

        foreach ($churches as $church) {
            $this->line("\nChurch: {$church->name}");

            $peopleWithRecentAttendance = AttendanceRecord::whereHas('attendance', function ($q) use ($church, $cutoffDate) {
                $q->where('church_id', $church->id)
                    ->where('date', '>=', $cutoffDate);
            })
                ->where('present', true)
                ->pluck('person_id')
                ->unique();

            $absentPeople = Person::where('church_id', $church->id)
                ->whereNotIn('id', $peopleWithRecentAttendance)
                ->whereHas('attendanceRecords', function ($q) {
                    $q->where('present', true);
                })
                ->get();

            if ($absentPeople->isEmpty()) {
                $this->line("  No alerts.");
                continue;
            }

            $this->warn("  People missing for {$weeks}+ weeks:");

            foreach ($absentPeople as $person) {
                $lastAttendance = AttendanceRecord::where('person_id', $person->id)
                    ->where('present', true)
                    ->whereHas('attendance')
                    ->latest()
                    ->first();

                $lastDate = $lastAttendance?->attendance?->date?->format('d.m.Y') ?? 'Never';
                $weeksAgo = $lastAttendance?->attendance?->date
                    ? now()->diffInWeeks($lastAttendance->attendance->date)
                    : '?';

                $this->line("    - {$person->full_name} (last: {$lastDate}, {$weeksAgo} weeks ago)");
            }
        }

        return self::SUCCESS;
    }
}
