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

        // Send birthday reminders to leaders at 8:00 AM
        $schedule->command('app:send-birthday-reminders')
            ->dailyAt('08:00')
            ->description('Send birthday reminders to leaders and admins');

        // Send task deadline reminders at 9:00 AM
        $schedule->command('app:send-task-reminders')
            ->dailyAt('09:00')
            ->description('Send task deadline reminders to assignees');

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

        // Sync exchange rates from NBU (after 10:00 when NBU publishes rates)
        $schedule->command('exchange-rates:sync')
            ->dailyAt('10:30')
            ->description('Sync exchange rates from NBU');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
