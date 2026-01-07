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
     * Sync events to Google Calendar
     */
    public function sync(Request $request)
    {
        $user = auth()->user();
        $church = $this->getCurrentChurch();

        $calendarId = $request->input('calendar_id', 'primary');

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
}
