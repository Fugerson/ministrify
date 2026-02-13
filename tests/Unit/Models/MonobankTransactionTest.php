<?php

namespace Tests\Unit\Models;

use App\Models\Church;
use App\Models\MonobankTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonobankTransactionTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        $this->church = Church::factory()->create();
    }

    // ==================
    // Amount Conversion
    // ==================

    public function test_amount_uah_converts_from_kopiykas(): void
    {
        $tx = MonobankTransaction::factory()->forChurch($this->church)->create([
            'amount' => 150075,
        ]);

        $this->assertEquals(1500.75, $tx->amount_uah);
    }

    public function test_amount_uah_absolute_for_negative(): void
    {
        $tx = MonobankTransaction::factory()->forChurch($this->church)->create([
            'amount' => -50000,
        ]);

        $this->assertEquals(500.0, $tx->amount_uah);
    }

    public function test_balance_uah(): void
    {
        $tx = MonobankTransaction::factory()->forChurch($this->church)->create([
            'balance' => 10000000,
        ]);

        $this->assertEquals(100000.0, $tx->balance_uah);
    }

    public function test_balance_uah_null_when_no_balance(): void
    {
        $tx = MonobankTransaction::factory()->forChurch($this->church)->create([
            'balance' => null,
        ]);

        $this->assertNull($tx->balance_uah);
    }

    // ==================
    // Formatted Amount
    // ==================

    public function test_formatted_amount_income(): void
    {
        $tx = MonobankTransaction::factory()->forChurch($this->church)->income()->create([
            'amount' => 150000,
        ]);

        $formatted = $tx->formatted_amount;
        $this->assertStringContainsString('+', $formatted);
        $this->assertStringContainsString('₴', $formatted);
    }

    public function test_formatted_amount_expense(): void
    {
        $tx = MonobankTransaction::factory()->forChurch($this->church)->expense()->create([
            'amount' => -50000,
        ]);

        $formatted = $tx->formatted_amount;
        $this->assertStringContainsString('-', $formatted);
        $this->assertStringContainsString('₴', $formatted);
    }

    // ==================
    // Counterpart Display
    // ==================

    public function test_counterpart_display_with_name(): void
    {
        $tx = MonobankTransaction::factory()->forChurch($this->church)->create([
            'counterpart_name' => 'ТОВ Компанія',
            'description' => 'Оплата послуг',
        ]);

        $this->assertEquals('ТОВ Компанія', $tx->counterpart_display);
    }

    public function test_counterpart_display_falls_back_to_description(): void
    {
        $tx = MonobankTransaction::factory()->forChurch($this->church)->create([
            'counterpart_name' => null,
            'description' => 'Оплата послуг',
        ]);

        $this->assertEquals('Оплата послуг', $tx->counterpart_display);
    }

    public function test_counterpart_display_unknown(): void
    {
        $tx = MonobankTransaction::factory()->forChurch($this->church)->create([
            'counterpart_name' => null,
            'description' => null,
        ]);

        $this->assertEquals('Невідомий відправник', $tx->counterpart_display);
    }

    // ==================
    // MCC Categories
    // ==================

    public function test_mcc_category_key_utilities(): void
    {
        $this->assertEquals('utilities', MonobankTransaction::getMccCategoryKey(4900));
    }

    public function test_mcc_category_key_groceries(): void
    {
        $this->assertEquals('groceries', MonobankTransaction::getMccCategoryKey(5411));
    }

    public function test_mcc_category_key_restaurants(): void
    {
        $this->assertEquals('restaurants', MonobankTransaction::getMccCategoryKey(5812));
    }

    public function test_mcc_category_key_other_for_unknown(): void
    {
        $this->assertEquals('other', MonobankTransaction::getMccCategoryKey(9999));
    }

    public function test_mcc_category_key_null_for_null(): void
    {
        $this->assertNull(MonobankTransaction::getMccCategoryKey(null));
    }

    public function test_mcc_category_name(): void
    {
        $this->assertEquals('Комунальні', MonobankTransaction::getMccCategoryName(4900));
        $this->assertEquals('Продукти', MonobankTransaction::getMccCategoryName(5411));
    }

    // ==================
    // createFromMonoData
    // ==================

    public function test_create_from_mono_data(): void
    {
        $data = [
            'id' => 'tx-unique-id-123',
            'amount' => 50000,
            'balance' => 1000000,
            'time' => now()->timestamp,
            'description' => 'Поповнення',
            'mcc' => 4829,
            'currencyCode' => '980',
        ];

        $tx = MonobankTransaction::createFromMonoData($this->church->id, $data);

        $this->assertEquals('tx-unique-id-123', $tx->mono_id);
        $this->assertEquals(50000, $tx->amount);
        $this->assertTrue($tx->is_income);
        $this->assertEquals($this->church->id, $tx->church_id);
    }

    public function test_create_from_mono_data_expense(): void
    {
        $data = [
            'id' => 'tx-expense-456',
            'amount' => -30000,
            'balance' => 970000,
            'time' => now()->timestamp,
            'description' => 'Покупка',
            'mcc' => 5411,
        ];

        $tx = MonobankTransaction::createFromMonoData($this->church->id, $data);

        $this->assertFalse($tx->is_income);
    }

    // ==================
    // Scopes
    // ==================

    public function test_unprocessed_income_scope(): void
    {
        MonobankTransaction::factory()->forChurch($this->church)->income()->create([
            'is_processed' => false,
            'is_ignored' => false,
        ]);
        MonobankTransaction::factory()->forChurch($this->church)->income()->processed()->create();
        MonobankTransaction::factory()->forChurch($this->church)->expense()->create();

        $this->assertCount(1, MonobankTransaction::unprocessedIncome()->get());
    }
}
