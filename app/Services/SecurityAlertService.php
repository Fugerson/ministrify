<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SecurityAlertService
{
    protected array $sensitiveQueryParams = [
        'token', 'key', 'secret', 'password', 'api_key', 'access_token',
        'refresh_token', 'auth', 'credential', 'session', 'csrf', '_token',
        'signature', 'hash', 'reset', 'verify',
    ];

    public function alert(string $type, string $message, array $context = []): void
    {
        if (!config('security.alerts.enabled', true)) {
            return;
        }

        $ip = $context['ip'] ?? request()->ip();
        $cacheKey = "security_alert:{$type}:{$ip}";
        $cooldown = config('security.alerts.cooldown_seconds', 60);

        // Deduplication: increment counter if already alerted recently
        if (Cache::has($cacheKey)) {
            Cache::increment($cacheKey . ':count');
            return;
        }

        // Mark this type+IP as alerted
        Cache::put($cacheKey, true, $cooldown);
        Cache::put($cacheKey . ':count', 1, $cooldown);

        // Log to security channel
        Log::channel('security')->warning($message, array_merge($context, ['type' => $type]));

        // Send Telegram alert
        $this->sendTelegram($type, $message, $context);
    }

    protected function sendTelegram(string $type, string $message, array $context): void
    {
        $token = config('services.telegram.alert_bot_token');
        $chatId = config('services.telegram.alert_chat_id');

        if (!$token || !$chatId) {
            return;
        }

        $emojis = [
            'sql_injection' => '💉',
            'xss' => '🔴',
            'path_traversal' => '📂',
            'scanner' => '🤖',
            'mass_404' => '🔍',
            'brute_force' => '🔐',
        ];

        $labels = [
            'sql_injection' => 'SQL Injection',
            'xss' => 'XSS',
            'path_traversal' => 'Path Traversal',
            'scanner' => 'Scanner/Bot',
            'mass_404' => 'Mass 404',
            'brute_force' => 'Brute Force',
        ];

        $emoji = $emojis[$type] ?? '🚨';
        $label = $labels[$type] ?? $type;
        $ip = $context['ip'] ?? request()->ip();
        $url = $this->sanitizeUrl($context['url'] ?? request()->fullUrl());
        $details = $this->sanitizeDetails($context['details'] ?? $message);
        $time = now()->format('Y-m-d H:i:s');

        $text = "🚨 *АТАКА: {$label}* {$emoji}\n"
            . "🌐 IP: `{$ip}`\n"
            . "🔗 URL: `{$url}`\n"
            . "📝 {$details}\n"
            . "⏰ {$time}";

        try {
            Http::timeout(5)->post(
                "https://api.telegram.org/bot{$token}/sendMessage",
                [
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => 'Markdown',
                ]
            );
        } catch (\Throwable $e) {
            Log::channel('security')->error('Failed to send Telegram alert', [
                'error' => $e->getMessage(),
                'type' => $type,
            ]);
        }
    }

    protected function sanitizeUrl(string $url): string
    {
        $parsed = parse_url($url);
        if (!$parsed || empty($parsed['query'])) {
            return mb_substr($url, 0, 200);
        }

        parse_str($parsed['query'], $params);

        foreach ($params as $key => &$value) {
            if ($this->isSensitiveParam($key)) {
                $value = '***';
            }
        }

        $clean = ($parsed['path'] ?? '/');
        if ($params) {
            $clean .= '?' . http_build_query($params);
        }

        return mb_substr($clean, 0, 200);
    }

    protected function isSensitiveParam(string $key): bool
    {
        $lower = strtolower($key);
        foreach ($this->sensitiveQueryParams as $sensitive) {
            if ($lower === $sensitive || str_contains($lower, $sensitive)) {
                return true;
            }
        }
        return false;
    }

    protected function sanitizeDetails(string $details): string
    {
        return mb_substr($details, 0, 300);
    }

    public static function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '***';
        }

        $name = $parts[0];
        $domain = $parts[1];

        if (mb_strlen($name) <= 2) {
            $masked = $name[0] . '***';
        } else {
            $masked = $name[0] . str_repeat('*', mb_strlen($name) - 2) . mb_substr($name, -1);
        }

        return $masked . '@' . $domain;
    }
}
