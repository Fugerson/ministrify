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

            // Multi-calendar mappings with backward compatibility
            $mappings = $settings['calendars']
                ?? (!empty($settings['calendar_id'])
                    ? [['calendar_id' => $settings['calendar_id'], 'ministry_id' => $settings['ministry_id'] ?? null]]
                    : [['calendar_id' => 'primary', 'ministry_id' => null]]);

            $church = $user->church;
            if (!$church) {
                continue;
            }

            $this->line("- {$user->name} ({$church->name}), " . count($mappings) . " calendar(s)...");

            $userSynced = false;

            foreach ($mappings as $mapping) {
                $calendarId = $mapping['calendar_id'] ?? 'primary';
                $ministryId = $mapping['ministry_id'] ?? null;

                try {
                    $result = $googleCalendar->fullSync($user, $church, $calendarId, $ministryId);

                    if ($result['success']) {
                        $toGoogle = $result['to_google'];
                        $fromGoogle = $result['from_google'];
                        $this->info("  [{$calendarId}] OK: →G {$toGoogle['created']}+{$toGoogle['updated']}, ←G {$fromGoogle['created']}+{$fromGoogle['updated']}");
                        $userSynced = true;
                    } else {
                        $this->error("  [{$calendarId}] Error: " . ($result['error'] ?? 'Unknown'));
                        $errors++;
                    }
                } catch (\Exception $e) {
                    Log::error('SyncGoogleCalendar command error', [
                        'user_id' => $user->id,
                        'calendar_id' => $calendarId,
                        'error' => $e->getMessage(),
                    ]);
                    $this->error("  [{$calendarId}] Exception: {$e->getMessage()}");
                    $errors++;
                }
            }

            if ($userSynced) {
                // Save last_synced_at
                $userSettings = $user->settings ?? [];
                $userSettings['google_calendar']['last_synced_at'] = now()->toISOString();
                $user->update(['settings' => $userSettings]);
                $synced++;
            }
        }

        $this->info("Done: {$synced} synced, {$errors} errors");

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
