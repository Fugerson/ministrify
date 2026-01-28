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

            Log::error('Google OAuth error', ['response' => $response->body()]);
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
        } catch (\Exception $e) {
            Log::error('Google token refresh failed', ['error' => $e->getMessage()]);
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
                return null;
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

            Log::error('Failed to create Google event', ['response' => $response->body()]);
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
                    $event->update(['google_event_id' => $result['id']]);
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
        $startDateTime = Carbon::parse($event->date->format('Y-m-d'));
        if ($event->time) {
            $startDateTime = $startDateTime->setTimeFromTimeString($event->time->format('H:i:s'));
        }

        $endDateTime = (clone $startDateTime)->addHour();

        $googleEvent = [
            'summary' => $event->title,
            'description' => $this->buildEventDescription($event),
            'start' => [
                'dateTime' => $startDateTime->toRfc3339String(),
                'timeZone' => 'Europe/Kiev',
            ],
            'end' => [
                'dateTime' => $endDateTime->toRfc3339String(),
                'timeZone' => 'Europe/Kiev',
            ],
        ];

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

        if ($event->description) {
            $lines[] = "";
            $lines[] = $event->description;
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

            Log::error('Failed to fetch Google events', ['response' => $response->body()]);
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

            // Update existing event
            $existingEvent->update(array_merge($eventData, [
                'google_synced_at' => now(),
                'google_sync_status' => 'synced',
            ]));

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
            $duplicate->update([
                'google_event_id' => $googleEvent['id'],
                'google_calendar_id' => $calendarId,
                'google_synced_at' => now(),
                'google_sync_status' => 'synced',
            ]);
            return 'updated';
        }

        // Create new event
        Event::create(array_merge($eventData, [
            'church_id' => $church->id,
            'ministry_id' => $ministryId,
            'google_event_id' => $googleEvent['id'],
            'google_calendar_id' => $calendarId,
            'google_synced_at' => now(),
            'google_sync_status' => 'synced',
        ]));

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

        return [
            'title' => $summary,
            'date' => $date,
            'time' => $time,
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
            'to_google' => ['created' => 0, 'updated' => 0, 'deleted' => 0, 'failed' => 0],
            'from_google' => ['created' => 0, 'updated' => 0, 'skipped' => 0],
            'errors' => [],
        ];

        // 1. Push local events to Google (Ministrify → Google)
        $localEvents = Event::where('church_id', $church->id)
            ->where('date', '>=', now()->subMonth())
            ->where('date', '<=', now()->addMonths(6))
            ->when($ministryId, fn($q) => $q->where('ministry_id', $ministryId))
            ->get();

        foreach ($localEvents as $event) {
            try {
                $result = $this->syncEventToGoogle($accessToken, $calendarId, $event);
                $results['to_google'][$result]++;
            } catch (\Exception $e) {
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
                $event->update([
                    'google_synced_at' => now(),
                    'google_sync_status' => 'synced',
                ]);
                return 'updated';
            }

            // If update fails (event deleted in Google?), try to create
            $event->update(['google_event_id' => null]);
        }

        // Create new event in Google
        $result = $this->createEvent($accessToken, $calendarId, $event);
        if ($result && isset($result['id'])) {
            $event->update([
                'google_event_id' => $result['id'],
                'google_calendar_id' => $calendarId,
                'google_synced_at' => now(),
                'google_sync_status' => 'synced',
            ]);
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
                $event->update([
                    'google_event_id' => null,
                    'google_calendar_id' => null,
                    'google_synced_at' => null,
                    'google_sync_status' => null,
                ]);
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
        $event->update([
            'google_event_id' => null,
            'google_calendar_id' => null,
            'google_synced_at' => null,
            'google_sync_status' => null,
        ]);
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
}
