<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Send reminders based on event-level settings
        $schedule->command('app:send-reminders')
            ->everyFifteenMinutes()
            ->description('Send event reminders based on each event\'s settings');

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
