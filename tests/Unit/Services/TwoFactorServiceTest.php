<?php

namespace Tests\Unit\Services;

use App\Models\Church;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TwoFactorServiceTest extends TestCase
{
    use RefreshDatabase;

    private TwoFactorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TwoFactorService();
    }

    // ==================
    // Key Generation
    // ==================

    public function test_generate_secret_key_is_16_chars(): void
    {
        $key = $this->service->generateSecretKey();

        $this->assertEquals(16, strlen($key));
    }

    public function test_generate_secret_key_is_base32(): void
    {
        $key = $this->service->generateSecretKey();

        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $key);
    }

    public function test_generate_secret_key_is_unique(): void
    {
        $key1 = $this->service->generateSecretKey();
        $key2 = $this->service->generateSecretKey();

        $this->assertNotEquals($key1, $key2);
    }

    // ==================
    // QR Code URL
    // ==================

    public function test_get_qr_code_url_format(): void
    {
        $church = Church::factory()->create();
        $user = User::factory()->create([
            'church_id' => $church->id,
            'email' => 'test@example.com',
        ]);

        $url = $this->service->getQrCodeUrl($user, 'TESTSECRETKEY123');

        $this->assertStringStartsWith('otpauth://totp/', $url);
        $this->assertStringContainsString('test%40example.com', $url);
        $this->assertStringContainsString('secret=TESTSECRETKEY123', $url);
    }

    // ==================
    // Verification
    // ==================

    public function test_verify_correct_code(): void
    {
        $secret = $this->service->generateSecretKey();

        // Generate the expected code for current time
        $timestamp = floor(time() / 30);
        $code = $this->generateTestCode($secret, $timestamp);

        $this->assertTrue($this->service->verify($secret, $code));
    }

    public function test_verify_wrong_code(): void
    {
        $secret = $this->service->generateSecretKey();

        $this->assertFalse($this->service->verify($secret, '000000'));
    }

    // ==================
    // Recovery Codes
    // ==================

    public function test_generate_recovery_codes(): void
    {
        $codes = $this->service->generateRecoveryCodes();

        $this->assertCount(8, $codes);
    }

    public function test_recovery_codes_format(): void
    {
        $codes = $this->service->generateRecoveryCodes();

        foreach ($codes as $code) {
            // Format: xxxx-xxxx
            $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}$/', $code);
        }
    }

    public function test_verify_recovery_code_removes_used_code(): void
    {
        $church = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $church->id]);
        $codes = $this->service->generateRecoveryCodes();
        $user->two_factor_recovery_codes = encrypt(json_encode($codes));
        $user->save();

        $codeToUse = $codes[0];
        $result = $this->service->verifyRecoveryCode($user, $codeToUse);

        $this->assertTrue($result);

        // Second use should fail
        $user->refresh();
        $result2 = $this->service->verifyRecoveryCode($user, $codeToUse);
        $this->assertFalse($result2);
    }

    public function test_verify_invalid_recovery_code(): void
    {
        $church = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $church->id]);
        $user->two_factor_recovery_codes = encrypt(json_encode(['abcd-efgh']));
        $user->save();

        $this->assertFalse($this->service->verifyRecoveryCode($user, 'wrong-code'));
    }

    // ==================
    // Helper
    // ==================

    private function generateTestCode(string $secret, int $timestamp): string
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

    private function base32Decode(string $input): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output = '';
        $buffer = 0;
        $bitsLeft = 0;

        for ($i = 0; $i < strlen($input); $i++) {
            $val = strpos($alphabet, strtoupper($input[$i]));
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
}
