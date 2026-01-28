<?php

namespace App\Observers;

use App\Models\Event;
use App\Services\GoogleCalendarService;
use Illuminate\Support\Facades\Log;

class EventObserver
{
    /**
     * Handle the Event "updated" event.
     * Sync changes to Google Calendar if connected.
     */
    public function updated(Event $event): void
    {
        // Skip if no Google sync or if being synced FROM Google
        if (!$event->google_event_id || !$event->google_calendar_id) {
            return;
        }

        // Skip if only google_* fields were updated (avoid infinite loop)
        $changedFields = array_keys($event->getChanges());
        $googleFields = ['google_event_id', 'google_calendar_id', 'google_synced_at', 'google_sync_status'];
        if (empty(array_diff($changedFields, $googleFields))) {
            return;
        }

        $this->syncToGoogle($event);
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

        // Get user who can sync (church admin or event creator)
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
     * Sync event to Google Calendar
     */
    protected function syncToGoogle(Event $event): void
    {
        $user = $this->getSyncUser($event);
        if (!$user) {
            return;
        }

        try {
            $service = app(GoogleCalendarService::class);
            $accessToken = $service->getValidToken($user);

            if (!$accessToken) {
                return;
            }

            $result = $service->updateEvent(
                $accessToken,
                $event->google_calendar_id,
                $event->google_event_id,
                $event
            );

            if ($result) {
                // Update sync timestamp without triggering observer again
                Event::withoutEvents(function () use ($event) {
                    $event->update([
                        'google_synced_at' => now(),
                        'google_sync_status' => 'synced',
                    ]);
                });
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
        // Find a user from the same church with Google Calendar connected
        return \App\Models\User::where('church_id', $event->church_id)
            ->whereNotNull('settings->google_calendar->access_token')
            ->first();
    }
}
