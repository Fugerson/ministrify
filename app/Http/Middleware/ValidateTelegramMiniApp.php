<?php

namespace App\Http\Middleware;

use App\Models\Person;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateTelegramMiniApp
{
    public function handle(Request $request, Closure $next): Response
    {
        $initData = $request->header('X-Telegram-Init-Data', '');

        // In local/testing — skip validation, use fallback person
        if (app()->environment('local', 'testing')) {
            $person = $this->parsePerson($initData) ?? Person::whereNotNull('telegram_chat_id')->first();

            if (!$person) {
                return response()->json(['error' => 'No linked person found'], 401);
            }

            $request->attributes->set('tma_person', $person);
            return $next($request);
        }

        if (empty($initData)) {
            return response()->json(['error' => 'Missing initData'], 401);
        }

        // Parse initData parameters
        parse_str($initData, $params);

        if (empty($params['hash'])) {
            return response()->json(['error' => 'Missing hash'], 401);
        }

        $hash = $params['hash'];

        // Build data_check_string: all params except hash, sorted by key, joined with \n
        $checkParams = $params;
        unset($checkParams['hash']);
        ksort($checkParams);

        $dataCheckString = collect($checkParams)
            ->map(fn($value, $key) => "{$key}={$value}")
            ->implode("\n");

        // Validate HMAC-SHA-256
        $botToken = config('services.telegram.bot_token');
        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $calculatedHash = bin2hex(hash_hmac('sha256', $dataCheckString, $secretKey, true));

        if (!hash_equals($calculatedHash, $hash)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Check auth_date freshness (1 hour)
        if (isset($params['auth_date'])) {
            $authDate = (int) $params['auth_date'];
            if (time() - $authDate > 3600) {
                return response()->json(['error' => 'Init data expired'], 401);
            }
        }

        // Extract user from initData
        $person = $this->parsePerson($initData);

        if (!$person) {
            return response()->json(['error' => 'Account not linked. Please link your Telegram in Ministrify profile.'], 403);
        }

        $request->attributes->set('tma_person', $person);

        return $next($request);
    }

    private function parsePerson(string $initData): ?Person
    {
        if (empty($initData)) {
            return null;
        }

        parse_str($initData, $params);

        if (empty($params['user'])) {
            return null;
        }

        $userData = json_decode($params['user'], true);

        if (!$userData || empty($userData['id'])) {
            return null;
        }

        return Person::where('telegram_chat_id', (string) $userData['id'])->first();
    }
}
