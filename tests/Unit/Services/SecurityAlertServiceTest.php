<?php

namespace Tests\Unit\Services;

use App\Services\SecurityAlertService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SecurityAlertServiceTest extends TestCase
{
    private SecurityAlertService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->service = new SecurityAlertService();
    }

    // ==================
    // Alert
    // ==================

    public function test_alert_logs_to_security_channel(): void
    {
        config(['security.alerts.enabled' => true]);

        $logMock = \Mockery::mock();
        $logMock->shouldReceive('warning')->once();

        Log::shouldReceive('channel')
            ->with('security')
            ->andReturn($logMock)
            ->once();

        $this->service->alert('xss', 'XSS attempt detected', [
            'ip' => '1.2.3.4',
            'url' => 'https://example.com/test',
        ]);
    }

    public function test_alert_deduplicates_within_cooldown(): void
    {
        config(['security.alerts.enabled' => true]);
        config(['security.alerts.cooldown_seconds' => 60]);

        // Pre-seed cache to simulate a previous alert (so the first call is also deduplicated)
        $cacheKey = 'security_alert:xss:1.2.3.4';
        Cache::put($cacheKey, true, 60);
        Cache::put($cacheKey . ':count', 1, 60);

        Log::shouldReceive('channel')->never();

        // Both calls should be deduplicated
        $this->service->alert('xss', 'XSS attempt', ['ip' => '1.2.3.4']);
        $this->service->alert('xss', 'XSS attempt again', ['ip' => '1.2.3.4']);
    }

    public function test_alert_disabled_does_nothing(): void
    {
        config(['security.alerts.enabled' => false]);

        Log::shouldReceive('channel')->never();

        $this->service->alert('xss', 'Test');
    }

    // ==================
    // sanitizeUrl
    // ==================

    public function test_sanitize_url_masks_sensitive_params(): void
    {
        $method = new \ReflectionMethod(SecurityAlertService::class, 'sanitizeUrl');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, 'https://example.com/path?token=secret123&page=1');

        $this->assertStringContainsString('page=1', $result);
        $this->assertStringNotContainsString('secret123', $result);
        $this->assertStringContainsString('token=%2A%2A%2A', $result);
    }

    public function test_sanitize_url_truncates_long_url(): void
    {
        $method = new \ReflectionMethod(SecurityAlertService::class, 'sanitizeUrl');
        $method->setAccessible(true);

        $longUrl = 'https://example.com/' . str_repeat('a', 300);
        $result = $method->invoke($this->service, $longUrl);

        $this->assertLessThanOrEqual(200, strlen($result));
    }

    public function test_sanitize_url_without_query(): void
    {
        $method = new \ReflectionMethod(SecurityAlertService::class, 'sanitizeUrl');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, 'https://example.com/path');
        $this->assertStringContainsString('example.com', $result);
    }

    // ==================
    // maskEmail
    // ==================

    public function test_mask_email_standard(): void
    {
        $result = SecurityAlertService::maskEmail('john@example.com');
        $this->assertEquals('j**n@example.com', $result);
    }

    public function test_mask_email_short_name(): void
    {
        $result = SecurityAlertService::maskEmail('ab@example.com');
        $this->assertEquals('a***@example.com', $result);
    }

    public function test_mask_email_invalid(): void
    {
        $result = SecurityAlertService::maskEmail('notanemail');
        $this->assertEquals('***', $result);
    }

    public function test_mask_email_long_name(): void
    {
        $result = SecurityAlertService::maskEmail('alexander@example.com');
        $this->assertStringStartsWith('a', $result);
        $this->assertStringEndsWith('r@example.com', $result);
        $this->assertStringContainsString('*', $result);
    }
}
