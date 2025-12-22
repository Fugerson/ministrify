<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class TwoFactorService
{
    /**
     * Generate a new secret key
     */
    public function generateSecretKey(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 16; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $secret;
    }

    /**
     * Generate QR code URL for Google Authenticator
     */
    public function getQrCodeUrl(User $user, string $secret): string
    {
        $appName = urlencode(config('app.name', 'ChurchHub'));
        $email = urlencode($user->email);

        return "otpauth://totp/{$appName}:{$email}?secret={$secret}&issuer={$appName}";
    }

    /**
     * Generate QR code as SVG
     */
    public function getQrCodeSvg(string $url): string
    {
        // Use a simple QR code generation
        // In production, you might want to use a library like BaconQrCode
        return $this->generateQrCodeSvg($url);
    }

    /**
     * Verify TOTP code
     */
    public function verify(string $secret, string $code): bool
    {
        // Allow 30 second window before and after
        $timestamp = floor(time() / 30);

        for ($i = -1; $i <= 1; $i++) {
            $expectedCode = $this->generateTOTP($secret, $timestamp + $i);
            if (hash_equals($expectedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate TOTP code
     */
    private function generateTOTP(string $secret, int $timestamp): string
    {
        $secretKey = $this->base32Decode($secret);
        $time = pack('N*', 0, $timestamp);

        $hash = hash_hmac('sha1', $time, $secretKey, true);
        $offset = ord(substr($hash, -1)) & 0x0F;

        $code = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        ) % 1000000;

        return str_pad((string) $code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Decode base32 string
     */
    private function base32Decode(string $input): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output = '';
        $buffer = 0;
        $bitsLeft = 0;

        for ($i = 0; $i < strlen($input); $i++) {
            $char = $input[$i];
            if ($char === '=') break;

            $val = strpos($alphabet, strtoupper($char));
            if ($val === false) continue;

            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $output;
    }

    /**
     * Generate recovery codes
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = Str::random(4) . '-' . Str::random(4);
        }
        return $codes;
    }

    /**
     * Verify recovery code
     */
    public function verifyRecoveryCode(User $user, string $code): bool
    {
        $codes = json_decode(decrypt($user->two_factor_recovery_codes), true) ?? [];

        foreach ($codes as $index => $storedCode) {
            if (hash_equals($storedCode, $code)) {
                // Remove used code
                unset($codes[$index]);
                $user->two_factor_recovery_codes = encrypt(json_encode(array_values($codes)));
                $user->save();
                return true;
            }
        }

        return false;
    }

    /**
     * Simple QR code SVG generator
     */
    private function generateQrCodeSvg(string $data): string
    {
        // This is a placeholder - in production use BaconQrCode
        // For now, return a link to an external QR generator API
        $encodedData = urlencode($data);
        return '<img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . $encodedData . '" alt="QR Code" class="mx-auto">';
    }
}
