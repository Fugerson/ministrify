<?php

namespace App\Http\Controllers;

use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GoogleCalendarController extends Controller
{
    protected GoogleCalendarService $googleCalendar;

    public function __construct(GoogleCalendarService $googleCalendar)
    {
        $this->googleCalendar = $googleCalendar;
    }

    /**
     * Redirect to Google OAuth
     */
    public function redirect()
    {
        if (!$this->googleCalendar->isConfigured()) {
            return back()->with('error', 'Google Calendar integration is not configured.');
        }

        $state = csrf_token();
        session(['google_oauth_state' => $state]);

        return redirect($this->googleCalendar->getAuthUrl($state));
    }

    /**
     * Handle OAuth callback
     */
    public function callback(Request $request)
    {
        // Verify state
        if ($request->state !== session('google_oauth_state')) {
            return redirect()->route('settings.index')
                ->with('error', 'Invalid OAuth state.');
        }

        if ($request->has('error')) {
            return redirect()->route('settings.index')
                ->with('error', 'Google authorization was denied.');
        }

        $code = $request->get('code');
        if (!$code) {
            return redirect()->route('settings.index')
                ->with('error', 'No authorization code received.');
        }

        $tokens = $this->googleCalendar->exchangeCode($code);
        if (!$tokens) {
            return redirect()->route('settings.index')
                ->with('error', 'Failed to exchange authorization code.');
        }

        // Save tokens to user settings (store church_id for cross-church isolation)
        $user = auth()->user();
        $settings = $user->settings ?? [];
        $settings['google_calendar'] = [
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'] ?? null,
            'expires_at' => time() + ($tokens['expires_in'] ?? 3600),
            'connected_at' => now()->toISOString(),
            'church_id' => $user->church_id,
        ];
        $user->update(['settings' => $settings]);

        // Clear session state
        session()->forget('google_oauth_state');

        return redirect()->route('settings.index')
            ->with('success', 'Google Calendar connected successfully!');
    }

    /**
     * Disconnect Google Calendar
     */
    public function disconnect()
    {
        $user = auth()->user();
        $settings = $user->settings ?? [];
        unset($settings['google_calendar']);
        $user->update(['settings' => $settings]);

        return back()->with('success', 'Google Calendar disconnected.');
    }

    /**
     * List available calendars
     */
    public function calendars()
    {
        $user = auth()->user();
        $accessToken = $this->googleCalendar->getValidToken($user);

        if (!$accessToken) {
            return response()->json(['error' => 'Not connected to Google Calendar'], 401);
        }

        $raw = $this->googleCalendar->listCalendars($accessToken);

        $calendars = collect($raw)
            ->filter(fn($c) => ($c['id'] ?? '') !== 'primary')
            ->map(function ($c) {
                $id = $c['id'] ?? '';
                $role = $c['accessRole'] ?? 'reader';
                $isImport = str_contains($id, '@import.calendar.google.com');
                $isHoliday = str_contains($id, '#holiday@group.v.calendar.google.com');
                $canSync = !$isImport && !$isHoliday && in_array($role, ['owner', 'writer']);

                return [
                    'id' => $id,
                    'summary' => $c['summary'] ?? $id,
                    'can_sync' => $canSync,
                ];
            })
            ->sortByDesc('can_sync')
            ->values()
            ->toArray();

        return response()->json(['calendars' => $calendars]);
    }

    /**
     * Sync events to Google Calendar (one-way: Ministrify → Google)
     */
    public function sync(Request $request)
    {
        $user = auth()->user();
        $church = $this->getCurrentChurch();

        $calendarId = $request->input('calendar_id', 'primary');

        // Save selected calendar_id for auto-sync
        $this->saveSelectedCalendar($user, $calendarId);

        $result = $this->googleCalendar->syncChurchEvents($user, $church, $calendarId);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => "Synced: {$result['created']} created, {$result['updated']} updated",
                'details' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'] ?? 'Sync failed',
        ], 400);
    }

    /**
     * Full two-way sync between Ministrify and Google Calendar
     */
    public function fullSync(Request $request)
    {
        set_time_limit(120);

        $user = auth()->user();
        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'calendar_id' => 'required|string',
            'ministry_id' => ['nullable', 'integer', Rule::exists('ministries', 'id')->where('church_id', auth()->user()->church_id)],
        ]);

        $ministryId = $validated['ministry_id'] ?? null;

        // Save selected calendar_id and ministry_id for auto-sync
        $this->saveSelectedCalendar($user, $validated['calendar_id'], $ministryId);

        try {
            $result = $this->googleCalendar->fullSync(
                $user,
                $church,
                $validated['calendar_id'],
                $ministryId
            );
        } catch (\Exception $e) {
            \Log::error('fullSync controller error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'Помилка синхронізації: ' . $e->getMessage(),
            ], 500);
        }

        if ($result['success']) {
            // Save last_synced_at
            $settings = $user->settings ?? [];
            $settings['google_calendar']['last_synced_at'] = now()->toISOString();
            $user->update(['settings' => $settings]);

            $toGoogle = $result['to_google'];
            $fromGoogle = $result['from_google'];

            $toTotal = $toGoogle['created'] + $toGoogle['updated'] + ($toGoogle['deleted'] ?? 0);
            $fromTotal = $fromGoogle['created'] + $fromGoogle['updated'];
            $toSkipped = $toGoogle['skipped'] ?? 0;

            if ($toTotal === 0 && $fromTotal === 0 && $toSkipped > 0) {
                $message = "Всі події вже синхронізовані ({$toSkipped} подій)";
            } else {
                $parts = [];
                if ($toGoogle['created'] > 0) $parts[] = "{$toGoogle['created']} → Google";
                if ($toGoogle['updated'] > 0) $parts[] = "{$toGoogle['updated']} оновлено в Google";
                if (($toGoogle['deleted'] ?? 0) > 0) $parts[] = "{$toGoogle['deleted']} видалено з Google";
                if ($fromGoogle['created'] > 0) $parts[] = "{$fromGoogle['created']} ← Google";
                if ($fromGoogle['updated'] > 0) $parts[] = "{$fromGoogle['updated']} оновлено з Google";
                $message = $parts ? "Синхронізовано: " . implode(', ', $parts) : "Немає змін";
            }
            if (($toGoogle['failed'] ?? 0) > 0) {
                $message .= " | {$toGoogle['failed']} помилок";
            }
            if (!empty($result['errors'])) {
                $message .= " | " . implode('; ', array_slice($result['errors'], 0, 3));
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'details' => $result,
                ]);
            }

            return back()->with('success', $message);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Sync failed',
            ], 400);
        }

        return back()->with('error', $result['error'] ?? 'Синхронізація не вдалась');
    }

    /**
     * Import events from Google Calendar (one-way: Google → Ministrify)
     */
    public function importFromGoogle(Request $request)
    {
        $user = auth()->user();
        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'calendar_id' => 'required|string',
            'ministry_id' => ['nullable', 'integer', Rule::exists('ministries', 'id')->where('church_id', auth()->user()->church_id)],
        ]);

        $result = $this->googleCalendar->importFromGoogle(
            $user,
            $church,
            $validated['calendar_id'],
            $validated['ministry_id'] ?? null
        );

        if ($result['success']) {
            $message = "Імпортовано: {$result['created']} створено, {$result['updated']} оновлено, {$result['skipped']} пропущено";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'details' => $result,
                ]);
            }

            return back()->with('success', $message);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Import failed',
            ], 400);
        }

        return back()->with('error', $result['error'] ?? 'Імпорт не вдався');
    }

    /**
     * Preview import - detect conflicts before importing
     */
    public function previewImport(Request $request)
    {
        $user = auth()->user();
        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'calendar_id' => 'required|string',
            'ministry_id' => ['nullable', 'integer', Rule::exists('ministries', 'id')->where('church_id', auth()->user()->church_id)],
        ]);

        $result = $this->googleCalendar->previewImport(
            $user,
            $church,
            $validated['calendar_id'],
            $validated['ministry_id'] ?? null
        );

        return response()->json($result);
    }

    /**
     * Get calendar mappings from user settings (with backward compatibility)
     */
    protected function getCalendarMappings($user): array
    {
        $gc = $user->settings['google_calendar'] ?? [];
        if (!empty($gc['calendars'])) {
            return $gc['calendars'];
        }
        // Backward compatibility: old single-calendar format → array
        if (!empty($gc['calendar_id'])) {
            return [['calendar_id' => $gc['calendar_id'], 'ministry_id' => $gc['ministry_id'] ?? null]];
        }
        return [['calendar_id' => 'primary', 'ministry_id' => null]];
    }

    /**
     * Save calendar mappings to user settings
     */
    public function saveCalendars(Request $request)
    {
        $validated = $request->validate([
            'calendars' => 'required|array|min:1',
            'calendars.*.calendar_id' => 'required|string',
            'calendars.*.ministry_id' => ['nullable', 'integer', Rule::exists('ministries', 'id')->where('church_id', auth()->user()->church_id)],
        ]);

        // Deduplicate by calendar_id
        $seen = [];
        $calendars = [];
        foreach ($validated['calendars'] as $mapping) {
            if (!in_array($mapping['calendar_id'], $seen)) {
                $seen[] = $mapping['calendar_id'];
                $calendars[] = [
                    'calendar_id' => $mapping['calendar_id'],
                    'ministry_id' => $mapping['ministry_id'] ?? null,
                ];
            }
        }

        $user = auth()->user();
        $settings = $user->settings ?? [];

        if (isset($settings['google_calendar'])) {
            $settings['google_calendar']['calendars'] = $calendars;
            $settings['google_calendar']['church_id'] = $user->church_id;
            // Remove old single-calendar fields
            unset($settings['google_calendar']['calendar_id']);
            unset($settings['google_calendar']['ministry_id']);
            $user->update(['settings' => $settings]);
        }

        return response()->json(['success' => true, 'calendars' => $calendars]);
    }

    /**
     * Full sync ALL calendar mappings
     */
    public function fullSyncAll(Request $request)
    {
        set_time_limit(120);

        $user = auth()->user();
        $church = $this->getCurrentChurch();
        $mappings = $this->getCalendarMappings($user);

        $aggregated = [
            'to_google' => ['created' => 0, 'updated' => 0, 'deleted' => 0, 'skipped' => 0, 'failed' => 0],
            'from_google' => ['created' => 0, 'updated' => 0],
        ];
        $errors = [];
        $allSuccess = true;

        foreach ($mappings as $mapping) {
            $calendarId = $mapping['calendar_id'] ?? 'primary';
            $ministryId = $mapping['ministry_id'] ?? null;

            try {
                $result = $this->googleCalendar->fullSync($user, $church, $calendarId, $ministryId);

                if ($result['success']) {
                    foreach (['created', 'updated', 'deleted', 'skipped', 'failed'] as $key) {
                        $aggregated['to_google'][$key] += $result['to_google'][$key] ?? 0;
                    }
                    foreach (['created', 'updated'] as $key) {
                        $aggregated['from_google'][$key] += $result['from_google'][$key] ?? 0;
                    }
                    if (!empty($result['errors'])) {
                        $errors = array_merge($errors, $result['errors']);
                    }
                } else {
                    $allSuccess = false;
                    $errors[] = ($result['error'] ?? 'Unknown error') . " (calendar: {$calendarId})";
                }
            } catch (\Exception $e) {
                \Log::error('fullSyncAll error', ['calendar_id' => $calendarId, 'error' => $e->getMessage()]);
                $allSuccess = false;
                $errors[] = $e->getMessage() . " (calendar: {$calendarId})";
            }
        }

        // Save last_synced_at + sync result log
        $settings = $user->settings ?? [];
        $settings['google_calendar']['last_synced_at'] = now()->toISOString();
        $settings['google_calendar']['last_sync_result'] = [
            'to_google' => $aggregated['to_google'],
            'from_google' => $aggregated['from_google'],
            'at' => now()->toISOString(),
            'calendars_count' => count($mappings),
        ];
        $user->update(['settings' => $settings]);

        $toGoogle = $aggregated['to_google'];
        $fromGoogle = $aggregated['from_google'];
        $toTotal = $toGoogle['created'] + $toGoogle['updated'] + $toGoogle['deleted'];
        $fromTotal = $fromGoogle['created'] + $fromGoogle['updated'];

        if ($toTotal === 0 && $fromTotal === 0 && $toGoogle['skipped'] > 0) {
            $message = "Всі події вже синхронізовані ({$toGoogle['skipped']} подій)";
        } else {
            $parts = [];
            if ($toGoogle['created'] > 0) $parts[] = "{$toGoogle['created']} → Google";
            if ($toGoogle['updated'] > 0) $parts[] = "{$toGoogle['updated']} оновлено в Google";
            if ($toGoogle['deleted'] > 0) $parts[] = "{$toGoogle['deleted']} видалено з Google";
            if ($fromGoogle['created'] > 0) $parts[] = "{$fromGoogle['created']} ← Google";
            if ($fromGoogle['updated'] > 0) $parts[] = "{$fromGoogle['updated']} оновлено з Google";
            $message = $parts ? "Синхронізовано: " . implode(', ', $parts) : "Немає змін";
        }
        if ($toGoogle['failed'] > 0) {
            $message .= " | {$toGoogle['failed']} помилок";
        }
        if (!empty($errors)) {
            $message .= " | " . implode('; ', array_slice($errors, 0, 3));
        }

        return response()->json([
            'success' => $allSuccess,
            'message' => $message,
            'details' => $aggregated,
        ]);
    }

    /**
     * Save selected calendar ID and ministry ID to user settings for auto-sync
     */
    protected function saveSelectedCalendar($user, string $calendarId, ?int $ministryId = null): void
    {
        $settings = $user->settings ?? [];
        if (isset($settings['google_calendar'])) {
            $settings['google_calendar']['calendar_id'] = $calendarId;
            $settings['google_calendar']['ministry_id'] = $ministryId;
            $settings['google_calendar']['church_id'] = $user->church_id;
            $user->update(['settings' => $settings]);
        }
    }

    /**
     * Import with conflict resolution
     */
    public function importWithResolution(Request $request)
    {
        $user = auth()->user();
        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'calendar_id' => 'required|string',
            'ministry_id' => ['nullable', 'integer', Rule::exists('ministries', 'id')->where('church_id', auth()->user()->church_id)],
            'resolutions' => 'required|array',
            'resolutions.*.google_event_id' => 'required|string',
            'resolutions.*.action' => 'required|in:skip,import,replace',
            'resolutions.*.local_event_id' => 'nullable|integer',
        ]);

        $result = $this->googleCalendar->importWithResolution(
            $user,
            $church,
            $validated['calendar_id'],
            $validated['ministry_id'] ?? null,
            $validated['resolutions']
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => "Імпортовано: {$result['imported']} подій, пропущено: {$result['skipped']}, замінено: {$result['replaced']}",
                'details' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'] ?? 'Import failed',
        ], 400);
    }

    /**
     * Unlink all events from Google Calendar (keep events, clear google_* fields)
     */
    public function unlinkGoogleEvents()
    {
        $church = $this->getCurrentChurch();

        $count = \App\Models\Event::where('church_id', $church->id)
            ->whereNotNull('google_event_id')
            ->update([
                'google_event_id' => null,
                'google_calendar_id' => null,
                'google_synced_at' => null,
                'google_sync_status' => null,
            ]);

        return response()->json([
            'success' => true,
            'message' => "Відв'язано {$count} подій від Google Calendar",
            'count' => $count,
        ]);
    }

    /**
     * Delete events (for testing)
     */
    public function deleteEvents(Request $request)
    {
        $validated = $request->validate([
            'scope' => 'required|in:synced,imported,all',
        ]);

        $church = $this->getCurrentChurch();
        $query = \App\Models\Event::where('church_id', $church->id);

        switch ($validated['scope']) {
            case 'synced':
                $query->whereNotNull('google_event_id');
                break;
            case 'imported':
                // Events that have google_event_id but were not created locally
                // (no way to distinguish perfectly, so delete events with google_event_id
                // that have description containing 'Створено в Ministrify' = local, else imported)
                $query->whereNotNull('google_event_id')
                    ->where(function ($q) {
                        $q->whereNull('notes')
                          ->orWhere('notes', 'not like', '%Створено в Ministrify%');
                    });
                break;
            case 'all':
                // All events for this church
                break;
        }

        $count = $query->count();
        $query->delete();

        return response()->json([
            'success' => true,
            'message' => "Видалено {$count} подій",
        ]);
    }
}
