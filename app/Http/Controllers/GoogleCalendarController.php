<?php

namespace App\Http\Controllers;

use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;

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

        // Save tokens to user settings
        $user = auth()->user();
        $settings = $user->settings ?? [];
        $settings['google_calendar'] = [
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'] ?? null,
            'expires_at' => time() + ($tokens['expires_in'] ?? 3600),
            'connected_at' => now()->toISOString(),
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

        $calendars = $this->googleCalendar->listCalendars($accessToken);

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
            'ministry_id' => 'nullable|integer|exists:ministries,id',
        ]);

        // Save selected calendar_id for auto-sync
        $this->saveSelectedCalendar($user, $validated['calendar_id']);

        try {
            $result = $this->googleCalendar->fullSync(
                $user,
                $church,
                $validated['calendar_id'],
                $validated['ministry_id'] ?? null
            );
        } catch (\Exception $e) {
            \Log::error('fullSync controller error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error' => 'Помилка синхронізації: ' . $e->getMessage(),
            ], 500);
        }

        if ($result['success']) {
            $toGoogle = $result['to_google'];
            $fromGoogle = $result['from_google'];

            $message = "Синхронізовано: ";
            $message .= "→ Google: {$toGoogle['created']} створено, {$toGoogle['updated']} оновлено";
            if (($toGoogle['deleted'] ?? 0) > 0) {
                $message .= ", {$toGoogle['deleted']} видалено";
            }
            if (($toGoogle['failed'] ?? 0) > 0) {
                $message .= ", {$toGoogle['failed']} помилок";
            }
            $skipped = $toGoogle['skipped'] ?? 0;
            $message .= "; ← Google: {$fromGoogle['created']} імпортовано, {$fromGoogle['updated']} оновлено";
            if (!empty($result['errors'])) {
                $message .= " | Помилки: " . implode('; ', array_slice($result['errors'], 0, 3));
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
            'ministry_id' => 'nullable|integer|exists:ministries,id',
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
            'ministry_id' => 'nullable|integer|exists:ministries,id',
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
     * Save selected calendar ID to user settings for auto-sync
     */
    protected function saveSelectedCalendar($user, string $calendarId): void
    {
        $settings = $user->settings ?? [];
        if (isset($settings['google_calendar'])) {
            $settings['google_calendar']['calendar_id'] = $calendarId;
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
            'ministry_id' => 'nullable|integer|exists:ministries,id',
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
