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

        // Monitor backup health every 6 hours
        $schedule->command('backup:monitor')
            ->everySixHours()
            ->description('Check backup size and age, alert if issues');

        // Clean old page visits
        $schedule->command('page-visits:clean')
            ->dailyAt('03:00')
            ->description('Delete page visits older than 30 days');

        // Sync Google Calendar for all connected users
        $schedule->command('app:sync-google-calendar')
            ->everyFifteenMinutes()
            ->description('Sync Google Calendar events for connected users');

        // Horizon metrics snapshots
        $schedule->command('horizon:snapshot')
            ->everyFiveMinutes()
            ->description('Capture Horizon queue metrics snapshot');

        // Pulse server metrics check
        $schedule->command('pulse:check')
            ->everyFiveMinutes()
            ->description('Record Pulse server metrics');

        // Telescope data pruning (keep 48 hours)
        $schedule->command('telescope:prune --hours=48')
            ->dailyAt('04:00')
            ->description('Prune Telescope entries older than 48 hours');

        // Prune permanently soft-deleted records
        $schedule->command('db:prune-soft-deletes')
            ->weeklyOn(0, '04:30')
            ->description('Permanently delete old soft-deleted records');

        // Clean orphaned records
        $schedule->command('db:clean-orphaned')
            ->weeklyOn(0, '05:00')
            ->description('Remove orphaned records from pivot tables');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
