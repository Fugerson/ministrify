<?php

namespace Tests\Unit\Services;

use App\Models\Church;
use App\Services\MonobankPersonalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use ReflectionMethod;
use Tests\TestCase;

class MonobankPersonalServiceTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        $this->church = Church::factory()->create();
    }

    public function test_is_configured_returns_false_without_token(): void
    {
        $service = new MonobankPersonalService($this->church);
        $this->assertFalse($service->isConfigured());
    }

    public function test_is_configured_returns_true_with_token(): void
    {
        $this->church->update(['monobank_token' => encrypt('test-token')]);
        $this->church->refresh();

        $service = new MonobankPersonalService($this->church);
        $this->assertTrue($service->isConfigured());
    }

    public function test_get_currency_name_uah(): void
    {
        $service = new MonobankPersonalService($this->church);
        $method = new ReflectionMethod(MonobankPersonalService::class, 'getCurrencyName');
        $method->setAccessible(true);

        $this->assertEquals('UAH', $method->invoke($service, 980));
    }

    public function test_get_currency_name_usd(): void
    {
        $service = new MonobankPersonalService($this->church);
        $method = new ReflectionMethod(MonobankPersonalService::class, 'getCurrencyName');
        $method->setAccessible(true);

        $this->assertEquals('USD', $method->invoke($service, 840));
    }

    public function test_get_currency_name_eur(): void
    {
        $service = new MonobankPersonalService($this->church);
        $method = new ReflectionMethod(MonobankPersonalService::class, 'getCurrencyName');
        $method->setAccessible(true);

        $this->assertEquals('EUR', $method->invoke($service, 978));
    }

    public function test_get_currency_name_unknown(): void
    {
        $service = new MonobankPersonalService($this->church);
        $method = new ReflectionMethod(MonobankPersonalService::class, 'getCurrencyName');
        $method->setAccessible(true);

        $result = $method->invoke($service, 999);
        $this->assertNotNull($result);
    }

    public function test_sync_transactions_skips_when_not_configured(): void
    {
        $service = new MonobankPersonalService($this->church);
        $result = $service->syncTransactions();

        // Should return gracefully without errors
        $this->assertIsArray($result);
    }

    public function test_sync_transactions_with_configured_church(): void
    {
        $this->church->update(['monobank_token' => encrypt('test-token')]);
        $this->church->refresh();

        Http::fake([
            'api.monobank.ua/*' => Http::response([
                [
                    'id' => 'test-tx-1',
                    'amount' => 50000,
                    'balance' => 1000000,
                    'time' => now()->timestamp,
                    'description' => 'Test Payment',
                    'mcc' => 4829,
                    'currencyCode' => 980,
                ],
            ]),
        ]);

        $service = new MonobankPersonalService($this->church);
        $result = $service->syncTransactions();

        $this->assertIsArray($result);
    }
}
