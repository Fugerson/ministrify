<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateTelegramWebhook
{
    /**
     * Telegram webhook IP ranges (CIDR notation)
     * https://core.telegram.org/bots/webhooks#the-short-version
     */
    private array $telegramIpRanges = [
        '149.154.160.0/20',
        '91.108.4.0/22',
    ];

    /**
     * Validate incoming Telegram webhook requests.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip validation in local/testing environment
        if (app()->environment('local', 'testing')) {
            return $next($request);
        }

        // Validate request structure
        if (!$this->hasValidStructure($request)) {
            return response()->json(['error' => 'Invalid request structure'], 400);
        }

        // Validate IP address (Telegram servers only)
        if (!$this->isFromTelegram($request)) {
            logger()->warning('Telegram webhook request from non-Telegram IP', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }

    /**
     * Check if request has valid Telegram webhook structure.
     */
    private function hasValidStructure(Request $request): bool
    {
        $data = $request->all();

        // Must have update_id
        if (!isset($data['update_id'])) {
            return false;
        }

        // Must have one of: message, callback_query, inline_query, etc.
        $validTypes = ['message', 'callback_query', 'inline_query', 'chosen_inline_result',
                       'channel_post', 'edited_message', 'edited_channel_post'];

        foreach ($validTypes as $type) {
            if (isset($data[$type])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if request IP is from Telegram servers.
     */
    private function isFromTelegram(Request $request): bool
    {
        $clientIp = $request->ip();

        foreach ($this->telegramIpRanges as $range) {
            if ($this->ipInRange($clientIp, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if IP is within CIDR range.
     */
    private function ipInRange(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr);

        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - (int) $bits);
        $subnet &= $mask;

        return ($ip & $mask) === $subnet;
    }
}
