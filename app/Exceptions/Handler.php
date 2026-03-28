<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if (app()->environment('production')) {
                $this->sendErrorToTelegram($e);
            }
        });

        // Handle CSRF token mismatch (419 Page Expired) - redirect to dashboard
        $this->renderable(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __('messages.session_expired_refresh')], 419);
            }

            return redirect()->route('dashboard')->with('error', __('messages.session_expired'));
        });

        // Handle expired/invalid verification links with a friendly page
        $this->renderable(function (InvalidSignatureException $e, $request) {
            // Check if this is a verification link
            if (str_contains($request->path(), 'email/verify')) {
                return response()->view('auth.verification-link-expired', [], 403);
            }

            // For other signed URLs, show generic error
            return response()->view('errors.403', [
                'message' => __('messages.link_invalid_or_expired'),
            ], 403);
        });
    }

    protected function sendErrorToTelegram(Throwable $e): void
    {
        $botToken = config('services.telegram.alert_bot_token');
        $chatId = config('services.telegram.alert_chat_id');

        if (! $botToken || ! $chatId) {
            return;
        }

        // Throttle: max 1 message per same error per 5 minutes
        $cacheKey = 'error_tg_' . md5($e->getFile() . $e->getLine() . $e->getMessage());
        if (cache()->has($cacheKey)) {
            return;
        }
        cache()->put($cacheKey, true, 300);

        $url = request()?->fullUrl() ?? 'console';
        $user = auth()->user();
        $userId = $user ? "#{$user->id} {$user->name}" : 'guest';

        $message = "🔴 *500 Error*\n\n";
        $message .= "📝 `" . mb_substr($e->getMessage(), 0, 200) . "`\n";
        $message .= "📁 `" . basename($e->getFile()) . ":" . $e->getLine() . "`\n";
        $message .= "🔗 `" . mb_substr($url, 0, 150) . "`\n";
        $message .= "👤 {$userId}\n";
        $message .= "⏰ " . now()->format('H:i:s d.m.Y');

        try {
            Http::timeout(5)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
            ]);
        } catch (\Exception $ex) {
            Log::warning('Failed to send error to Telegram: ' . $ex->getMessage());
        }
    }
}
