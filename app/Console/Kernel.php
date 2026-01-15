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

        // Sync Monobank transactions for churches with auto-sync enabled
        $schedule->command('monobank:sync')
            ->hourly()
            ->description('Sync Monobank transactions automatically');

        // Sync PrivatBank transactions for churches with auto-sync enabled
        $schedule->command('privatbank:sync')
            ->hourly()
            ->description('Sync PrivatBank transactions automatically');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
