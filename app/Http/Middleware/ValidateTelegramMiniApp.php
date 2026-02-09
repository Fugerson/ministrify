<?php

namespace App\Http\Middleware;

use App\Models\Person;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ValidateTelegramMiniApp
{
    public function handle(Request $request, Closure $next): Response
    {
        $initData = $request->header('X-Telegram-Init-Data', '');
        $authToken = $request->header('X-TMA-Auth-Token', '');

        // In local/testing — skip validation, use fallback person
        if (app()->environment('local', 'testing')) {
            $person = $this->parsePerson($initData)
                ?? $this->parseToken($authToken)
                ?? Person::whereNotNull('telegram_chat_id')->first();

            if (!$person) {
                return response()->json(['error' => 'No linked person found'], 401);
            }

            $request->attributes->set('tma_person', $person);
            return $next($request);
        }

        // Strategy 1: Validate initData (Telegram HMAC-SHA-256)
        if (!empty($initData)) {
            $person = $this->validateInitData($initData);
            if ($person) {
                $request->attributes->set('tma_person', $person);
                return $next($request);
            }
        }

        // Strategy 2: Validate auth token (fallback for clients that don't pass initData)
        if (!empty($authToken)) {
            $person = $this->parseToken($authToken);
            if ($person) {
                $request->attributes->set('tma_person', $person);
                return $next($request);
            }
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    private function validateInitData(string $initData): ?Person
    {
        parse_str($initData, $params);

        if (empty($params['hash'])) {
            return null;
        }

        $hash = $params['hash'];

        // Build data_check_string
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
            return null;
        }

        // Check auth_date freshness (1 hour)
        if (isset($params['auth_date'])) {
            $authDate = (int) $params['auth_date'];
            if (time() - $authDate > 3600) {
                return null;
            }
        }

        return $this->parsePerson($initData);
    }

    private function parseToken(string $token): ?Person
    {
        if (empty($token)) {
            return null;
        }

        $cached = Cache::get("tma_auth_{$token}");
        if (!$cached || empty($cached['person_id'])) {
            return null;
        }

        return Person::find($cached['person_id']);
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

    /**
     * Generate an auth token for a person (used by TelegramController).
     */
    public static function generateAuthToken(Person $person): string
    {
        $token = \Illuminate\Support\Str::random(40);

        Cache::put("tma_auth_{$token}", [
            'person_id' => $person->id,
        ], now()->addDays(30));

        return $token;
    }
}
