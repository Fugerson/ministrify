<?php

namespace App\Observers;

use App\Models\Event;
use App\Services\GoogleCalendarService;
use Illuminate\Support\Facades\Log;

class EventObserver
{
    /**
     * Handle the Event "created" event.
     * Auto-push new events to Google Calendar if any user has it connected.
     */
    public function created(Event $event): void
    {
        $this->pushToGoogle($event);
    }

    /**
     * Handle the Event "updated" event.
     * Sync changes to Google Calendar if connected.
     */
    public function updated(Event $event): void
    {
        // Skip if only google_* fields were updated (avoid infinite loop)
        $changedFields = array_keys($event->getChanges());
        $googleFields = ['google_event_id', 'google_calendar_id', 'google_synced_at', 'google_sync_status'];
        if (empty(array_diff($changedFields, $googleFields))) {
            return;
        }

        $this->pushToGoogle($event);
    }

    /**
     * Handle the Event "deleted" event.
     * Delete from Google Calendar if connected.
     */
    public function deleted(Event $event): void
    {
        if (!$event->google_event_id || !$event->google_calendar_id) {
            return;
        }

        $user = $this->getSyncUser($event);
        if (!$user) {
            return;
        }

        try {
            $service = app(GoogleCalendarService::class);
            $accessToken = $service->getValidToken($user);

            if ($accessToken) {
                $service->deleteEvent($accessToken, $event->google_calendar_id, $event->google_event_id);
            }
        } catch (\Exception $e) {
            Log::warning('EventObserver: Failed to delete from Google Calendar', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Push event to Google Calendar (create or update)
     */
    protected function pushToGoogle(Event $event): void
    {
        $user = $this->getSyncUser($event);
        if (!$user) {
            return;
        }

        $calendarId = $this->getCalendarId($user);
        if (!$calendarId) {
            return;
        }

        try {
            $service = app(GoogleCalendarService::class);
            $accessToken = $service->getValidToken($user);

            if (!$accessToken) {
                return;
            }

            if ($event->google_event_id && $event->google_calendar_id) {
                // Update existing Google event
                $result = $service->updateEvent(
                    $accessToken,
                    $event->google_calendar_id,
                    $event->google_event_id,
                    $event
                );

                if ($result) {
                    Event::withoutEvents(function () use ($event) {
                        $event->update([
                            'google_synced_at' => now(),
                            'google_sync_status' => 'synced',
                        ]);
                    });
                }
            } else {
                // Create new event in Google
                $result = $service->createEvent($accessToken, $calendarId, $event);

                if ($result && isset($result['id'])) {
                    Event::withoutEvents(function () use ($event, $result, $calendarId) {
                        $event->update([
                            'google_event_id' => $result['id'],
                            'google_calendar_id' => $calendarId,
                            'google_synced_at' => now(),
                            'google_sync_status' => 'synced',
                        ]);
                    });
                }
            }
        } catch (\Exception $e) {
            Log::warning('EventObserver: Failed to sync to Google Calendar', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get a user who can sync this event's church
     */
    protected function getSyncUser(Event $event): ?\App\Models\User
    {
        return \App\Models\User::where('church_id', $event->church_id)
            ->whereNotNull('settings->google_calendar->access_token')
            ->first();
    }

    /**
     * Get the calendar ID from user settings (saved during manual sync)
     */
    protected function getCalendarId(\App\Models\User $user): ?string
    {
        $settings = $user->settings ?? [];
        return $settings['google_calendar']['calendar_id'] ?? 'primary';
    }
}
