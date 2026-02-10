<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\GoogleCalendarService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncGoogleCalendar extends Command
{
    protected $signature = 'app:sync-google-calendar';

    protected $description = 'Sync Google Calendar events for all connected users';

    public function handle(GoogleCalendarService $googleCalendar): int
    {
        $users = User::whereNotNull('settings->google_calendar->access_token')
            ->whereNotNull('settings->google_calendar->calendar_id')
            ->get();

        if ($users->isEmpty()) {
            $this->info('No users with Google Calendar connected');
            return self::SUCCESS;
        }

        $this->info("Syncing {$users->count()} user(s)...");

        $synced = 0;
        $errors = 0;

        foreach ($users as $user) {
            $settings = $user->settings['google_calendar'] ?? [];
            $calendarId = $settings['calendar_id'] ?? null;
            $ministryId = $settings['ministry_id'] ?? null;

            if (!$calendarId) {
                continue;
            }

            $church = $user->church;
            if (!$church) {
                continue;
            }

            $this->line("- {$user->name} ({$church->name})...");

            try {
                $result = $googleCalendar->fullSync($user, $church, $calendarId, $ministryId);

                if ($result['success']) {
                    // Save last_synced_at
                    $userSettings = $user->settings ?? [];
                    $userSettings['google_calendar']['last_synced_at'] = now()->toISOString();
                    $user->update(['settings' => $userSettings]);

                    $toGoogle = $result['to_google'];
                    $fromGoogle = $result['from_google'];
                    $this->info("  OK: →G {$toGoogle['created']}+{$toGoogle['updated']}, ←G {$fromGoogle['created']}+{$fromGoogle['updated']}");
                    $synced++;
                } else {
                    $this->error("  Error: " . ($result['error'] ?? 'Unknown'));
                    $errors++;
                }
            } catch (\Exception $e) {
                Log::error('SyncGoogleCalendar command error', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("  Exception: {$e->getMessage()}");
                $errors++;
            }
        }

        $this->info("Done: {$synced} synced, {$errors} errors");

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
