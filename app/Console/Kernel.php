<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Send reminders for tomorrow's events
        $schedule->command('app:send-reminders --days=1')
            ->dailyAt('18:00')
            ->description('Send reminders for tomorrow\'s events');

        // Send reminders for today's events (2 hours before)
        $schedule->command('app:send-reminders --hours=2')
            ->everyThirtyMinutes()
            ->description('Send reminders 2 hours before events');

        // Generate recurring events
        $schedule->command('app:generate-recurring-events')
            ->weekly()
            ->description('Generate recurring events for next 4 weeks');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
