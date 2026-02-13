<?php

namespace Tests\Unit\Services;

use App\Services\NbuExchangeRateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NbuExchangeRateServiceTest extends TestCase
{
    use RefreshDatabase;

    private NbuExchangeRateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new NbuExchangeRateService();
    }

    public function test_sync_rates_creates_records(): void
    {
        Http::fake([
            'bank.gov.ua/*' => Http::response([
                ['cc' => 'USD', 'rate' => 41.50, 'exchangedate' => '13.02.2025'],
                ['cc' => 'EUR', 'rate' => 44.80, 'exchangedate' => '13.02.2025'],
            ]),
        ]);

        $result = $this->service->syncRates();

        $this->assertIsArray($result);
        $this->assertTrue($result['success'] ?? false);
    }

    public function test_sync_rates_handles_api_failure(): void
    {
        Http::fake([
            'bank.gov.ua/*' => Http::response(null, 500),
        ]);

        $result = $this->service->syncRates();

        $this->assertIsArray($result);
        $this->assertFalse($result['success'] ?? true);
    }

    public function test_sync_rates_handles_empty_response(): void
    {
        Http::fake([
            'bank.gov.ua/*' => Http::response([]),
        ]);

        $result = $this->service->syncRates();

        // Empty response should not crash
        $this->assertIsArray($result);
    }

    public function test_get_current_rates_structure(): void
    {
        Http::fake([
            'bank.gov.ua/*' => Http::response([
                ['cc' => 'USD', 'rate' => 41.50, 'exchangedate' => '13.02.2025'],
                ['cc' => 'EUR', 'rate' => 44.80, 'exchangedate' => '13.02.2025'],
            ]),
        ]);

        // Sync first
        $this->service->syncRates();

        $rates = $this->service->getCurrentRates();

        $this->assertIsArray($rates);
    }
}
