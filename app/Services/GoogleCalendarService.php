<?php

namespace App\Services;

use App\Models\Church;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class GoogleCalendarService
{
    protected const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    protected const CALENDAR_API_URL = 'https://www.googleapis.com/calendar/v3';
    protected const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    protected const SCOPES = 'https://www.googleapis.com/auth/calendar';

    protected ?string $clientId;
    protected ?string $clientSecret;
    protected ?string $redirectUri;

    public function __construct()
    {
        $this->clientId = config('services.google.client_id');
        $this->clientSecret = config('services.google.client_secret');
        $this->redirectUri = config('services.google.redirect_uri');
    }

    /**
     * Check if Google Calendar integration is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->clientId) && !empty($this->clientSecret);
    }

    /**
     * Get OAuth authorization URL
     */
    public function getAuthUrl(string $state = ''): string
    {
        $params = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => self::SCOPES,
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);

        return self::AUTH_URL . '?' . $params;
    }

    /**
     * Exchange authorization code for access token
     */
    public function exchangeCode(string $code): ?array
    {
        try {
            $response = Http::asForm()->post(self::TOKEN_URL, [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->redirectUri,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Google OAuth error', ['status' => $response->status()]);
        } catch (\Exception $e) {
            Log::error('Google OAuth exception', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Refresh access token
     */
    public function refreshToken(string $refreshToken): ?array
    {
        try {
            $response = Http::asForm()->post(self::TOKEN_URL, [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Google token refresh failed', [
                'status' => $response->status(),
            ]);
        } catch (\Exception $e) {
            Log::error('Google token refresh exception', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Get valid access token for user (refresh if needed)
     */
    public function getValidToken(User $user): ?string
    {
        $settings = $user->settings ?? [];
        $googleCalendar = $settings['google_calendar'] ?? null;

        if (!$googleCalendar || empty($googleCalendar['access_token'])) {
            return null;
        }

        // Check if token is expired
        $expiresAt = $googleCalendar['expires_at'] ?? 0;
        if (time() >= $expiresAt - 60) { // Refresh 1 minute before expiry
            if (empty($googleCalendar['refresh_token'])) {
                Log::warning('Google token expired and no refresh_token available', ['user_id' => $user->id]);
                return null;
            }

            // Use cache lock to prevent race condition on token refresh
            $lockKey = "google_token_refresh_{$user->id}";
            $lock = Cache::lock($lockKey, 30);

            try {
                if ($lock->get()) {
                    // Re-fetch user to get latest token (another process may have refreshed it)
                    $user->refresh();
                    $settings = $user->settings ?? [];
                    $googleCalendar = $settings['google_calendar'] ?? null;

                    // Check again if token was already refreshed
                    $expiresAt = $googleCalendar['expires_at'] ?? 0;
                    if (time() < $expiresAt - 60) {
                        return $googleCalendar['access_token'];
                    }

                    $newToken = $this->refreshToken($googleCalendar['refresh_token']);
                    if (!$newToken) {
                        return null;
                    }

                    // Update user settings with new token
                    $settings['google_calendar'] = array_merge($googleCalendar, [
                        'access_token' => $newToken['access_token'],
                        'expires_at' => time() + ($newToken['expires_in'] ?? 3600),
                    ]);
                    $user->update(['settings' => $settings]);

                    return $newToken['access_token'];
                } else {
                    // Another process is refreshing, wait and retry
                    usleep(500000); // 500ms
                    $user->refresh();
                    $settings = $user->settings ?? [];
                    $googleCalendar = $settings['google_calendar'] ?? null;
                    return $googleCalendar['access_token'] ?? null;
                }
            } finally {
                $lock->release();
            }
        }

        return $googleCalendar['access_token'];
    }

    /**
     * List user's calendars
     */
    public function listCalendars(string $accessToken): array
    {
        try {
            $response = Http::withToken($accessToken)
                ->get(self::CALENDAR_API_URL . '/users/me/calendarList');

            if ($response->successful()) {
                return $response->json()['items'] ?? [];
            }
        } catch (\Exception $e) {
            Log::error('Failed to list calendars', ['error' => $e->getMessage()]);
        }

        return [];
    }

    /**
     * Create event in Google Calendar
     */
    public function createEvent(string $accessToken, string $calendarId, Event $event): ?array
    {
        $googleEvent = $this->convertToGoogleEvent($event);

        try {
            $response = Http::withToken($accessToken)
                ->post(self::CALENDAR_API_URL . "/calendars/{$calendarId}/events", $googleEvent);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Failed to create Google event', ['status' => $response->status()]);
        } catch (\Exception $e) {
            Log::error('Google event creation error', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Update event in Google Calendar
     */
    public function updateEvent(string $accessToken, string $calendarId, string $googleEventId, Event $event): ?array
    {
        $googleEvent = $this->convertToGoogleEvent($event);

        try {
            $response = Http::withToken($accessToken)
                ->put(self::CALENDAR_API_URL . "/calendars/{$calendarId}/events/{$googleEventId}", $googleEvent);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Google event update error', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Delete event from Google Calendar
     */
    public function deleteEvent(string $accessToken, string $calendarId, string $googleEventId): bool
    {
        try {
            $response = Http::withToken($accessToken)
                ->delete(self::CALENDAR_API_URL . "/calendars/{$calendarId}/events/{$googleEventId}");

            return $response->successful() || $response->status() === 404;
        } catch (\Exception $e) {
            Log::error('Google event deletion error', ['error' => $e->getMessage()]);
        }

        return false;
    }

    /**
     * Sync church events to Google Calendar
     */
    public function syncChurchEvents(User $user, Church $church, string $calendarId): array
    {
        $accessToken = $this->getValidToken($user);
        if (!$accessToken) {
            return ['success' => false, 'error' => 'No valid access token'];
        }

        $results = [
            'created' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        // Get upcoming events
        $events = Event::where('church_id', $church->id)
            ->where('date', '>=', now())
            ->where('date', '<=', now()->addMonths(3))
            ->get();

        foreach ($events as $event) {
            $googleEventId = $event->google_event_id ?? null;

            if ($googleEventId) {
                // Update existing event
                $result = $this->updateEvent($accessToken, $calendarId, $googleEventId, $event);
                if ($result) {
                    $results['updated']++;
                } else {
                    $results['failed']++;
                }
            } else {
                // Create new event
                $result = $this->createEvent($accessToken, $calendarId, $event);
                if ($result && isset($result['id'])) {
                    Event::withoutEvents(function () use ($event, $result, $calendarId) {
                        $event->update([
                            'google_event_id' => $result['id'],
                            'google_calendar_id' => $calendarId,
                            'google_synced_at' => now(),
                            'google_sync_status' => 'synced',
                        ]);
                    });
                    $results['created']++;
                } else {
                    $results['failed']++;
                }
            }

            // Rate limiting
            usleep(100000); // 100ms
        }

        return array_merge(['success' => true], $results);
    }

    /**
     * Convert Ministrify event to Google Calendar event format
     */
    private function convertToGoogleEvent(Event $event): array
    {
        $googleEvent = [
            'summary' => $event->title,
            'description' => $this->buildEventDescription($event),
        ];

        // Determine if this is an all-day event (no time specified)
        $isAllDay = !$event->time;

        if ($isAllDay) {
            // All-day event - use date format
            $googleEvent['start'] = [
                'date' => $event->date->format('Y-m-d'),
                'timeZone' => 'Europe/Kyiv',
            ];

            // For all-day events, Google expects end date to be the day AFTER the last day
            $endDate = $event->end_date ?? $event->date;
            $googleEvent['end'] = [
                'date' => Carbon::parse($endDate)->addDay()->format('Y-m-d'),
                'timeZone' => 'Europe/Kyiv',
            ];
        } else {
            // Timed event - use dateTime format
            $startDateTime = Carbon::parse($event->date->format('Y-m-d'))
                ->setTimeFromTimeString($event->time->format('H:i:s'));

            $googleEvent['start'] = [
                'dateTime' => $startDateTime->toRfc3339String(),
                'timeZone' => 'Europe/Kyiv',
            ];

            // Calculate end time
            if ($event->end_date && $event->end_time) {
                $endDateTime = Carbon::parse($event->end_date->format('Y-m-d'))
                    ->setTimeFromTimeString($event->end_time->format('H:i:s'));
            } elseif ($event->end_time) {
                $endDateTime = Carbon::parse($event->date->format('Y-m-d'))
                    ->setTimeFromTimeString($event->end_time->format('H:i:s'));
            } elseif ($event->end_date) {
                // Multi-day event with times
                $endDateTime = Carbon::parse($event->end_date->format('Y-m-d'))
                    ->setTimeFromTimeString($event->time->format('H:i:s'));
            } else {
                // Default: 1 hour duration
                $endDateTime = (clone $startDateTime)->addHour();
            }

            $googleEvent['end'] = [
                'dateTime' => $endDateTime->toRfc3339String(),
                'timeZone' => 'Europe/Kyiv',
            ];
        }

        if ($event->location) {
            $googleEvent['location'] = $event->location;
        }

        // Add color based on ministry
        if ($event->ministry && $event->ministry->color) {
            $googleEvent['colorId'] = $this->mapColorToGoogleColorId($event->ministry->color);
        }

        return $googleEvent;
    }

    /**
     * Build event description with details
     */
    private function buildEventDescription(Event $event): string
    {
        $lines = [];

        if ($event->ministry) {
            $lines[] = "Служіння: {$event->ministry->name}";
        }

        if ($event->notes) {
            $lines[] = "";
            $lines[] = $event->notes;
        }

        $lines[] = "";
        $lines[] = "---";
        $lines[] = "Створено в Ministrify";

        return implode("\n", $lines);
    }

    /**
     * Map hex color to Google Calendar color ID
     */
    private function mapColorToGoogleColorId(string $hexColor): string
    {
        // Google Calendar has 11 predefined colors (1-11)
        // Map common colors to the closest Google color
        $colorMap = [
            '#3b82f6' => '9',  // Blue
            '#ef4444' => '11', // Red
            '#22c55e' => '10', // Green
            '#f59e0b' => '5',  // Yellow/Orange
            '#8b5cf6' => '1',  // Purple
            '#ec4899' => '4',  // Pink
            '#06b6d4' => '7',  // Cyan
            '#6b7280' => '8',  // Gray
        ];

        $hexColor = strtolower($hexColor);
        return $colorMap[$hexColor] ?? '9';
    }

    /**
     * Fetch events from Google Calendar
     */
    public function fetchEventsFromGoogle(string $accessToken, string $calendarId, ?Carbon $timeMin = null, ?Carbon $timeMax = null): array
    {
        $params = [
            'singleEvents' => 'true',
            'orderBy' => 'startTime',
            'maxResults' => 250,
        ];

        if ($timeMin) {
            $params['timeMin'] = $timeMin->toRfc3339String();
        } else {
            $params['timeMin'] = now()->subMonth()->toRfc3339String();
        }

        if ($timeMax) {
            $params['timeMax'] = $timeMax->toRfc3339String();
        } else {
            $params['timeMax'] = now()->addMonths(6)->toRfc3339String();
        }

        try {
            $response = Http::withToken($accessToken)
                ->get(self::CALENDAR_API_URL . "/calendars/{$calendarId}/events", $params);

            if ($response->successful()) {
                return $response->json()['items'] ?? [];
            }

            Log::error('Failed to fetch Google events', ['status' => $response->status()]);
        } catch (\Exception $e) {
            Log::error('Google events fetch error', ['error' => $e->getMessage()]);
        }

        return [];
    }

    /**
     * Get a single event from Google Calendar
     */
    public function getGoogleEvent(string $accessToken, string $calendarId, string $eventId): ?array
    {
        try {
            $response = Http::withToken($accessToken)
                ->get(self::CALENDAR_API_URL . "/calendars/{$calendarId}/events/{$eventId}");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Failed to get Google event', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Import events from Google Calendar to Ministrify
     */
    public function importFromGoogle(User $user, Church $church, string $calendarId, ?int $ministryId = null): array
    {
        $accessToken = $this->getValidToken($user);
        if (!$accessToken) {
            return ['success' => false, 'error' => 'No valid access token'];
        }

        $results = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $googleEvents = $this->fetchEventsFromGoogle($accessToken, $calendarId);

        foreach ($googleEvents as $googleEvent) {
            try {
                $result = $this->importSingleEvent($church, $googleEvent, $calendarId, $ministryId);
                $results[$result]++;
            } catch (\Exception $e) {
                $results['errors'][] = $e->getMessage();
            }

            usleep(50000); // 50ms rate limiting
        }

        return array_merge(['success' => true], $results);
    }

    /**
     * Import a single Google event to Ministrify
     */
    protected function importSingleEvent(Church $church, array $googleEvent, string $calendarId, ?int $ministryId): string
    {
        // Skip cancelled events
        if (($googleEvent['status'] ?? '') === 'cancelled') {
            return 'skipped';
        }

        // Parse event data
        $eventData = $this->parseGoogleEvent($googleEvent);
        if (!$eventData) {
            return 'skipped';
        }

        // Check if event already exists by google_event_id
        $existingEvent = Event::where('church_id', $church->id)
            ->where('google_event_id', $googleEvent['id'])
            ->first();

        if ($existingEvent) {
            // Check if Google event is newer
            $googleUpdated = isset($googleEvent['updated'])
                ? Carbon::parse($googleEvent['updated'])
                : now();

            if ($existingEvent->google_synced_at && $googleUpdated->lte($existingEvent->google_synced_at)) {
                return 'skipped'; // Local is newer or same
            }

            // Update existing event (suppress observer - already synced from Google)
            Event::withoutEvents(function () use ($existingEvent, $eventData) {
                $existingEvent->update(array_merge($eventData, [
                    'google_synced_at' => now(),
                    'google_sync_status' => 'synced',
                ]));
            });

            return 'updated';
        }

        // Check for duplicate by title and date
        $duplicate = Event::where('church_id', $church->id)
            ->where('title', $eventData['title'])
            ->whereDate('date', $eventData['date'])
            ->whereNull('google_event_id')
            ->first();

        if ($duplicate) {
            // Link existing event to Google
            Event::withoutEvents(function () use ($duplicate, $googleEvent, $calendarId) {
                $duplicate->update([
                    'google_event_id' => $googleEvent['id'],
                    'google_calendar_id' => $calendarId,
                    'google_synced_at' => now(),
                    'google_sync_status' => 'synced',
                ]);
            });
            return 'updated';
        }

        // Create new event (suppress observer - already exists in Google)
        Event::withoutEvents(function () use ($eventData, $church, $ministryId, $googleEvent, $calendarId) {
            Event::create(array_merge($eventData, [
                'church_id' => $church->id,
                'ministry_id' => $ministryId,
                'google_event_id' => $googleEvent['id'],
                'google_calendar_id' => $calendarId,
                'google_synced_at' => now(),
                'google_sync_status' => 'synced',
            ]));
        });

        return 'created';
    }

    /**
     * Parse Google event to Ministrify format
     */
    protected function parseGoogleEvent(array $googleEvent): ?array
    {
        $summary = $googleEvent['summary'] ?? null;
        if (!$summary) {
            return null;
        }

        // Parse start time
        $start = $googleEvent['start'] ?? [];
        $end = $googleEvent['end'] ?? [];

        $date = null;
        $time = null;
        $endDate = null;
        $endTime = null;

        if (isset($start['dateTime'])) {
            $startDt = Carbon::parse($start['dateTime']);
            $date = $startDt->format('Y-m-d');
            $time = $startDt->format('H:i');
        } elseif (isset($start['date'])) {
            $date = $start['date'];
            $time = null;
        } else {
            return null;
        }

        // Parse end time
        if (isset($end['dateTime'])) {
            $endDt = Carbon::parse($end['dateTime']);
            $endDate = $endDt->format('Y-m-d');
            $endTime = $endDt->format('H:i');
        } elseif (isset($end['date'])) {
            // For all-day events, Google sets end date to the day AFTER the last day
            // So we need to subtract 1 day
            $endDate = Carbon::parse($end['date'])->subDay()->format('Y-m-d');
            $endTime = null;
        }

        // If end_date equals start date, don't store it (single-day event)
        if ($endDate === $date) {
            $endDate = null;
        }

        return [
            'title' => $summary,
            'date' => $date,
            'end_date' => $endDate,
            'time' => $time,
            'end_time' => $endTime,
            'notes' => $googleEvent['description'] ?? null,
            'location' => $googleEvent['location'] ?? null,
        ];
    }

    /**
     * Full two-way sync between Ministrify and Google Calendar
     */
    public function fullSync(User $user, Church $church, string $calendarId, ?int $ministryId = null): array
    {
        $accessToken = $this->getValidToken($user);
        if (!$accessToken) {
            return ['success' => false, 'error' => 'No valid access token'];
        }

        $results = [
            'to_google' => ['created' => 0, 'updated' => 0, 'deleted' => 0, 'skipped' => 0, 'failed' => 0],
            'from_google' => ['created' => 0, 'updated' => 0, 'skipped' => 0],
            'errors' => [],
        ];

        // 1. Push local events to Google (Ministrify → Google)
        $localEvents = Event::where('church_id', $church->id)
            ->where('date', '>=', now()->subMonth())
            ->where('date', '<=', now()->addMonths(6))
            ->when($ministryId, fn($q) => $q->where('ministry_id', $ministryId))
            ->get();

        Log::info('fullSync: pushing ' . $localEvents->count() . ' events to Google', [
            'church_id' => $church->id,
            'calendar_id' => $calendarId,
            'ministry_id' => $ministryId,
            'unsynced' => $localEvents->whereNull('google_event_id')->count(),
        ]);

        foreach ($localEvents as $event) {
            try {
                $result = $this->syncEventToGoogle($accessToken, $calendarId, $event);
                $results['to_google'][$result] = ($results['to_google'][$result] ?? 0) + 1;
            } catch (\Exception $e) {
                Log::error('fullSync push error', ['event_id' => $event->id, 'error' => $e->getMessage()]);
                $results['errors'][] = "Push {$event->id}: " . $e->getMessage();
                $results['to_google']['failed']++;
            }
            usleep(100000); // 100ms
        }

        // 2. Pull Google events to Ministrify (Google → Ministrify)
        $googleEvents = $this->fetchEventsFromGoogle($accessToken, $calendarId);

        foreach ($googleEvents as $googleEvent) {
            try {
                // Skip events that originated from Ministrify (have our marker)
                $description = $googleEvent['description'] ?? '';
                if (str_contains($description, 'Створено в Ministrify')) {
                    continue;
                }

                $result = $this->importSingleEvent($church, $googleEvent, $calendarId, $ministryId);
                $results['from_google'][$result]++;
            } catch (\Exception $e) {
                $results['errors'][] = "Pull {$googleEvent['id']}: " . $e->getMessage();
            }
            usleep(50000); // 50ms
        }

        // 3. Handle deleted events (events in Ministrify with google_event_id that no longer exist in Google)
        $this->handleDeletedGoogleEvents($accessToken, $calendarId, $church, $ministryId, $results);

        return array_merge(['success' => true], $results);
    }

    /**
     * Sync single event to Google Calendar
     */
    protected function syncEventToGoogle(string $accessToken, string $calendarId, Event $event): string
    {
        if ($event->google_event_id) {
            // Check if local event is newer
            if ($event->google_synced_at && $event->updated_at->lte($event->google_synced_at)) {
                return 'skipped';
            }

            // Update in Google
            $result = $this->updateEvent($accessToken, $calendarId, $event->google_event_id, $event);
            if ($result) {
                Event::withoutEvents(function () use ($event) {
                    $event->update([
                        'google_synced_at' => now(),
                        'google_sync_status' => 'synced',
                    ]);
                });
                return 'updated';
            }

            // If update fails (event deleted in Google?), try to create
            Event::withoutEvents(function () use ($event) {
                $event->update(['google_event_id' => null]);
            });
        }

        // Create new event in Google
        Log::info('syncEventToGoogle: creating new', ['event_id' => $event->id, 'title' => $event->title]);
        $result = $this->createEvent($accessToken, $calendarId, $event);
        if ($result && isset($result['id'])) {
            Event::withoutEvents(function () use ($event, $result, $calendarId) {
                $event->update([
                    'google_event_id' => $result['id'],
                    'google_calendar_id' => $calendarId,
                    'google_synced_at' => now(),
                    'google_sync_status' => 'synced',
                ]);
            });
            return 'created';
        }

        return 'failed';
    }

    /**
     * Handle events deleted from Google Calendar
     */
    protected function handleDeletedGoogleEvents(string $accessToken, string $calendarId, Church $church, ?int $ministryId, array &$results): void
    {
        $syncedEvents = Event::where('church_id', $church->id)
            ->whereNotNull('google_event_id')
            ->where('google_calendar_id', $calendarId)
            ->when($ministryId, fn($q) => $q->where('ministry_id', $ministryId))
            ->get();

        foreach ($syncedEvents as $event) {
            $googleEvent = $this->getGoogleEvent($accessToken, $calendarId, $event->google_event_id);

            if (!$googleEvent || ($googleEvent['status'] ?? '') === 'cancelled') {
                // Event was deleted in Google - mark as unsynced
                Event::withoutEvents(function () use ($event) {
                    $event->update([
                        'google_event_id' => null,
                        'google_calendar_id' => null,
                        'google_synced_at' => null,
                        'google_sync_status' => null,
                    ]);
                });
                $results['to_google']['deleted']++;
            }

            usleep(50000); // 50ms
        }
    }

    /**
     * Disconnect event from Google Calendar (unlink without deleting)
     */
    public function unlinkEvent(Event $event): void
    {
        Event::withoutEvents(function () use ($event) {
            $event->update([
                'google_event_id' => null,
                'google_calendar_id' => null,
                'google_synced_at' => null,
                'google_sync_status' => null,
            ]);
        });
    }

    /**
     * Delete event from Google and unlink
     */
    public function deleteAndUnlink(User $user, Event $event): bool
    {
        if (!$event->google_event_id || !$event->google_calendar_id) {
            return true;
        }

        $accessToken = $this->getValidToken($user);
        if (!$accessToken) {
            return false;
        }

        $deleted = $this->deleteEvent($accessToken, $event->google_calendar_id, $event->google_event_id);

        if ($deleted) {
            $this->unlinkEvent($event);
        }

        return $deleted;
    }

    /**
     * Preview import - detect conflicts before importing
     * Conflicts are detected by time overlap, not by title matching
     */
    public function previewImport(User $user, Church $church, string $calendarId, ?int $ministryId = null): array
    {
        $accessToken = $this->getValidToken($user);
        if (!$accessToken) {
            return ['success' => false, 'error' => 'No valid access token'];
        }

        $googleEvents = $this->fetchEventsFromGoogle($accessToken, $calendarId);

        $preview = [
            'new' => [],      // Events that don't exist locally
            'updates' => [],  // Events already linked by google_event_id
            'conflicts' => [], // Events that overlap in time with existing local events
        ];

        foreach ($googleEvents as $googleEvent) {
            // Skip cancelled events
            if (($googleEvent['status'] ?? '') === 'cancelled') {
                continue;
            }

            // Skip events that originated from Ministrify
            $description = $googleEvent['description'] ?? '';
            if (str_contains($description, 'Створено в Ministrify')) {
                continue;
            }

            $eventData = $this->parseGoogleEvent($googleEvent);
            if (!$eventData) {
                continue;
            }

            // Check if already linked by google_event_id
            $linkedEvent = Event::where('church_id', $church->id)
                ->where('google_event_id', $googleEvent['id'])
                ->first();

            if ($linkedEvent) {
                $preview['updates'][] = [
                    'google_event' => [
                        'id' => $googleEvent['id'],
                        'title' => $eventData['title'],
                        'date' => $eventData['date'],
                        'end_date' => $eventData['end_date'],
                        'time' => $eventData['time'],
                        'end_time' => $eventData['end_time'],
                        'location' => $eventData['location'],
                    ],
                    'local_event' => [
                        'id' => $linkedEvent->id,
                        'title' => $linkedEvent->title,
                        'date' => $linkedEvent->date->format('Y-m-d'),
                        'end_date' => $linkedEvent->end_date?->format('Y-m-d'),
                        'time' => $linkedEvent->time?->format('H:i'),
                        'end_time' => $linkedEvent->end_time?->format('H:i'),
                    ],
                ];
                continue;
            }

            // Find conflicts by time overlap
            $conflicts = $this->findConflictingEvents($church, $eventData, $ministryId);

            if ($conflicts->isNotEmpty()) {
                $preview['conflicts'][] = [
                    'google_event' => [
                        'id' => $googleEvent['id'],
                        'title' => $eventData['title'],
                        'date' => $eventData['date'],
                        'end_date' => $eventData['end_date'],
                        'time' => $eventData['time'],
                        'end_time' => $eventData['end_time'],
                        'location' => $eventData['location'],
                    ],
                    'conflicting_events' => $conflicts->map(fn($e) => [
                        'id' => $e->id,
                        'title' => $e->title,
                        'date' => $e->date->format('Y-m-d'),
                        'end_date' => $e->end_date?->format('Y-m-d'),
                        'time' => $e->time?->format('H:i'),
                        'end_time' => $e->end_time?->format('H:i'),
                        'ministry' => $e->ministry?->name,
                    ])->toArray(),
                ];
            } else {
                $preview['new'][] = [
                    'google_event' => [
                        'id' => $googleEvent['id'],
                        'title' => $eventData['title'],
                        'date' => $eventData['date'],
                        'end_date' => $eventData['end_date'],
                        'time' => $eventData['time'],
                        'end_time' => $eventData['end_time'],
                        'location' => $eventData['location'],
                    ],
                ];
            }
        }

        return [
            'success' => true,
            'preview' => $preview,
            'counts' => [
                'new' => count($preview['new']),
                'updates' => count($preview['updates']),
                'conflicts' => count($preview['conflicts']),
            ],
        ];
    }

    /**
     * Find events that conflict (overlap in time) with the given event data
     */
    protected function findConflictingEvents(Church $church, array $eventData, ?int $ministryId): \Illuminate\Support\Collection
    {
        $date = Carbon::parse($eventData['date']);
        $endDate = $eventData['end_date'] ? Carbon::parse($eventData['end_date']) : $date;

        $query = Event::where('church_id', $church->id)
            ->whereNull('google_event_id') // Only unlinked events
            ->where(function ($q) use ($date, $endDate) {
                // Event overlaps if:
                // local.start <= google.end AND local.end >= google.start
                $q->where(function ($inner) use ($date, $endDate) {
                    $inner->where('date', '<=', $endDate)
                        ->where(function ($w) use ($date) {
                            $w->where('end_date', '>=', $date)
                                ->orWhere(function ($orQ) use ($date) {
                                    $orQ->whereNull('end_date')
                                        ->where('date', '>=', $date);
                                });
                        });
                });
            });

        if ($ministryId) {
            $query->where('ministry_id', $ministryId);
        }

        return $query->with('ministry')->get();
    }

    /**
     * Import events with user-specified conflict resolution
     */
    public function importWithResolution(
        User $user,
        Church $church,
        string $calendarId,
        ?int $ministryId,
        array $resolutions
    ): array {
        $accessToken = $this->getValidToken($user);
        if (!$accessToken) {
            return ['success' => false, 'error' => 'No valid access token'];
        }

        $results = [
            'imported' => 0,
            'skipped' => 0,
            'replaced' => 0,
            'errors' => [],
        ];

        // Build a map of resolutions by google_event_id
        $resolutionMap = collect($resolutions)->keyBy('google_event_id');

        $googleEvents = $this->fetchEventsFromGoogle($accessToken, $calendarId);

        foreach ($googleEvents as $googleEvent) {
            $googleId = $googleEvent['id'];
            $resolution = $resolutionMap->get($googleId);

            if (!$resolution) {
                continue; // Not in resolution list, skip
            }

            $action = $resolution['action'];

            if ($action === 'skip') {
                $results['skipped']++;
                continue;
            }

            // Skip cancelled events
            if (($googleEvent['status'] ?? '') === 'cancelled') {
                $results['skipped']++;
                continue;
            }

            $eventData = $this->parseGoogleEvent($googleEvent);
            if (!$eventData) {
                $results['skipped']++;
                continue;
            }

            try {
                if ($action === 'replace' && !empty($resolution['local_event_id'])) {
                    // Replace: update existing local event and link to Google
                    $localEvent = Event::where('church_id', $church->id)
                        ->find($resolution['local_event_id']);

                    if ($localEvent) {
                        Event::withoutEvents(function () use ($localEvent, $eventData, $googleId, $calendarId) {
                            $localEvent->update(array_merge($eventData, [
                                'google_event_id' => $googleId,
                                'google_calendar_id' => $calendarId,
                                'google_synced_at' => now(),
                                'google_sync_status' => 'synced',
                            ]));
                        });
                        $results['replaced']++;
                    } else {
                        // Event not found, import as new
                        $this->createEventFromGoogle($church, $eventData, $googleId, $calendarId, $ministryId);
                        $results['imported']++;
                    }
                } else {
                    // Import as new event
                    $this->createEventFromGoogle($church, $eventData, $googleId, $calendarId, $ministryId);
                    $results['imported']++;
                }
            } catch (\Exception $e) {
                $results['errors'][] = "Event {$googleId}: " . $e->getMessage();
            }

            usleep(50000); // 50ms rate limiting
        }

        return array_merge(['success' => true], $results);
    }

    /**
     * Create a new event from Google Calendar data
     */
    protected function createEventFromGoogle(
        Church $church,
        array $eventData,
        string $googleId,
        string $calendarId,
        ?int $ministryId
    ): Event {
        return Event::withoutEvents(function () use ($eventData, $church, $ministryId, $googleId, $calendarId) {
            return Event::create(array_merge($eventData, [
                'church_id' => $church->id,
                'ministry_id' => $ministryId,
                'google_event_id' => $googleId,
                'google_calendar_id' => $calendarId,
                'google_synced_at' => now(),
                'google_sync_status' => 'synced',
            ]));
        });
    }
}
