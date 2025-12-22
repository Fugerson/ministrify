<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SecurityHelper
{
    /**
     * Sanitize a string for safe database storage.
     * Note: This is a fallback - always prefer Eloquent parameterized queries.
     */
    public static function sanitizeString(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        // Remove null bytes
        $input = str_replace("\0", '', $input);

        // Remove control characters except newlines and tabs
        $input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $input);

        return trim($input);
    }

    /**
     * Escape string for LIKE queries to prevent wildcard injection.
     */
    public static function escapeLike(string $value): string
    {
        return str_replace(
            ['%', '_', '\\'],
            ['\\%', '\\_', '\\\\'],
            $value
        );
    }

    /**
     * Generate a cryptographically secure random token.
     */
    public static function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Generate a human-readable secure code (for SMS/email verification).
     */
    public static function generateVerificationCode(int $length = 6): string
    {
        return strtoupper(Str::random($length));
    }

    /**
     * Check if a password is commonly used / weak.
     */
    public static function isWeakPassword(string $password): bool
    {
        $weakPasswords = [
            'password', '123456', '12345678', 'qwerty', 'abc123',
            'password1', 'password123', 'admin', 'letmein', 'welcome',
            'monkey', '1234567', 'dragon', 'master', 'login',
            'sunshine', 'princess', 'admin123', 'passw0rd', 'shadow',
        ];

        $normalized = strtolower(trim($password));

        return in_array($normalized, $weakPasswords, true);
    }

    /**
     * Log security-related event.
     */
    public static function logSecurityEvent(string $event, array $context = []): void
    {
        $context['ip'] = request()->ip();
        $context['user_agent'] = request()->userAgent();
        $context['user_id'] = auth()->id();
        $context['timestamp'] = now()->toIso8601String();

        Log::channel('security')->info("Security Event: {$event}", $context);
    }

    /**
     * Mask sensitive data for logging.
     */
    public static function maskSensitive(string $value, int $visibleChars = 4): string
    {
        $length = strlen($value);

        if ($length <= $visibleChars * 2) {
            return str_repeat('*', $length);
        }

        $start = substr($value, 0, $visibleChars);
        $end = substr($value, -$visibleChars);
        $masked = str_repeat('*', $length - ($visibleChars * 2));

        return $start . $masked . $end;
    }

    /**
     * Validate that a URL is safe (not javascript:, data:, etc.).
     */
    public static function isSafeUrl(?string $url): bool
    {
        if (empty($url)) {
            return true;
        }

        $dangerousSchemes = ['javascript:', 'data:', 'vbscript:', 'file:'];

        $lowerUrl = strtolower(trim($url));

        foreach ($dangerousSchemes as $scheme) {
            if (str_starts_with($lowerUrl, $scheme)) {
                return false;
            }
        }

        return filter_var($url, FILTER_VALIDATE_URL) !== false
            || str_starts_with($url, '/')
            || str_starts_with($url, '#');
    }

    /**
     * Hash a value for comparison (e.g., for timing-safe token comparison).
     */
    public static function hashForComparison(string $value): string
    {
        return hash('sha256', $value);
    }

    /**
     * Constant-time string comparison to prevent timing attacks.
     */
    public static function secureCompare(string $a, string $b): bool
    {
        return hash_equals($a, $b);
    }
}
