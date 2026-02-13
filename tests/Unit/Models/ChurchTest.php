<?php

namespace Tests\Unit\Models;

use App\Models\Church;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChurchTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        $this->church = Church::factory()->create([
            'initial_balance' => 5000,
        ]);
    }

    // ==================
    // Financial Methods
    // ==================

    public function test_total_income_sums_incoming_transactions(): void
    {
        Transaction::factory()->forChurch($this->church)->income()->completed()->create(['amount' => 1000]);
        Transaction::factory()->forChurch($this->church)->income()->completed()->create(['amount' => 2000]);

        $this->assertEquals(3000.0, $this->church->total_income);
    }

    public function test_total_income_excludes_pending_transactions(): void
    {
        Transaction::factory()->forChurch($this->church)->income()->completed()->create(['amount' => 1000]);
        Transaction::factory()->forChurch($this->church)->income()->pending()->create(['amount' => 5000]);

        $this->assertEquals(1000.0, $this->church->total_income);
    }

    public function test_total_expense_sums_outgoing_transactions(): void
    {
        Transaction::factory()->forChurch($this->church)->expense()->completed()->create(['amount' => 500]);
        Transaction::factory()->forChurch($this->church)->expense()->completed()->create(['amount' => 300]);

        $this->assertEquals(800.0, $this->church->total_expense);
    }

    public function test_current_balance_calculates_correctly(): void
    {
        Transaction::factory()->forChurch($this->church)->income()->completed()->create(['amount' => 3000]);
        Transaction::factory()->forChurch($this->church)->expense()->completed()->create(['amount' => 1000]);

        // initial_balance(5000) + income(3000) - expense(1000) = 7000
        $this->assertEquals(7000.0, $this->church->current_balance);
    }

    public function test_balance_breakdown_returns_correct_structure(): void
    {
        $breakdown = $this->church->getBalanceBreakdown();

        $this->assertArrayHasKey('initial_balance', $breakdown);
        $this->assertArrayHasKey('total_income', $breakdown);
        $this->assertArrayHasKey('total_expense', $breakdown);
        $this->assertArrayHasKey('current_balance', $breakdown);
        $this->assertEquals(5000.0, $breakdown['initial_balance']);
    }

    public function test_get_initial_balance_for_currency_from_json(): void
    {
        $this->church->update(['initial_balances' => ['UAH' => 10000, 'USD' => 500]]);
        $this->church->refresh();

        $this->assertEquals(500.0, $this->church->getInitialBalanceForCurrency('USD'));
        $this->assertEquals(10000.0, $this->church->getInitialBalanceForCurrency('UAH'));
    }

    public function test_get_initial_balance_falls_back_to_old_field_for_uah(): void
    {
        $this->church->update(['initial_balances' => null]);
        $this->church->refresh();

        $this->assertEquals(5000.0, $this->church->getInitialBalanceForCurrency('UAH'));
    }

    public function test_get_initial_balance_returns_zero_for_unknown_currency(): void
    {
        $this->assertEquals(0.0, $this->church->getInitialBalanceForCurrency('GBP'));
    }

    public function test_get_all_initial_balances_from_json(): void
    {
        $this->church->update(['initial_balances' => ['UAH' => 10000, 'USD' => 500]]);
        $this->church->refresh();

        $balances = $this->church->getAllInitialBalances();
        $this->assertEquals(['UAH' => 10000, 'USD' => 500], $balances);
    }

    public function test_get_all_initial_balances_fallback_to_old_field(): void
    {
        $this->church->update(['initial_balances' => null]);
        $this->church->refresh();

        $balances = $this->church->getAllInitialBalances();
        $this->assertEquals(['UAH' => 5000.0], $balances);
    }

    // ==================
    // Settings
    // ==================

    public function test_get_setting_returns_value(): void
    {
        $this->church->update(['settings' => ['notifications' => ['email' => true]]]);
        $this->church->refresh();

        $this->assertTrue($this->church->getSetting('notifications.email'));
    }

    public function test_get_setting_returns_default_when_missing(): void
    {
        $this->assertEquals('fallback', $this->church->getSetting('nonexistent.key', 'fallback'));
    }

    public function test_set_setting_saves_nested_value(): void
    {
        $this->church->setSetting('google_calendar.enabled', true);
        $this->church->refresh();

        $this->assertTrue($this->church->getSetting('google_calendar.enabled'));
    }

    public function test_is_notification_enabled_returns_true_by_default(): void
    {
        $this->assertTrue($this->church->isNotificationEnabled('some_unknown_key'));
    }

    public function test_is_notification_enabled_returns_configured_value(): void
    {
        $this->church->update(['settings' => ['notifications' => ['email' => false]]]);
        $this->church->refresh();

        $this->assertFalse($this->church->isNotificationEnabled('email'));
    }

    // ==================
    // Calendar Token
    // ==================

    public function test_get_calendar_token_generates_if_missing(): void
    {
        $this->assertNull($this->church->calendar_token);

        $token = $this->church->getCalendarToken();

        $this->assertNotEmpty($token);
        $this->assertEquals(32, strlen($token));
    }

    public function test_get_calendar_token_returns_existing(): void
    {
        $this->church->update(['calendar_token' => 'existing-token-12345678901234']);
        $this->church->refresh();

        $this->assertEquals('existing-token-12345678901234', $this->church->getCalendarToken());
    }

    public function test_regenerate_calendar_token_creates_new(): void
    {
        $oldToken = $this->church->getCalendarToken();
        $newToken = $this->church->regenerateCalendarToken();

        $this->assertNotEquals($oldToken, $newToken);
        $this->assertEquals(32, strlen($newToken));
    }

    // ==================
    // Theme Colors
    // ==================

    public function test_theme_colors_returns_palette(): void
    {
        $this->church->update(['primary_color' => '#3b82f6']);
        $this->church->refresh();

        $colors = $this->church->theme_colors;

        $this->assertIsArray($colors);
        $this->assertArrayHasKey('500', $colors);
        $this->assertEquals('#3b82f6', $colors['500']);
        $this->assertCount(10, $colors);
    }

    public function test_theme_colors_uses_default_when_no_color(): void
    {
        // primary_color is NOT NULL in DB; test the accessor default by
        // instantiating a model instance without persisting.
        $church = new Church(['primary_color' => null]);
        $colors = $church->theme_colors;
        $this->assertEquals('#3b82f6', $colors['500']);
    }

    // ==================
    // CSS Sanitization
    // ==================

    public function test_custom_css_blocks_javascript_protocol(): void
    {
        $this->church->setPublicSiteSetting('custom_css', 'div { background: url(javascript:alert(1)) }');
        $this->church->refresh();

        $css = $this->church->custom_css;
        $this->assertStringNotContainsString('javascript:', $css);
    }

    public function test_custom_css_blocks_expression(): void
    {
        $this->church->setPublicSiteSetting('custom_css', 'div { width: expression(alert(1)) }');
        $this->church->refresh();

        $css = $this->church->custom_css;
        $this->assertStringNotContainsString('expression(', $css);
    }

    public function test_custom_css_blocks_import(): void
    {
        $this->church->setPublicSiteSetting('custom_css', '@import url("evil.css"); body { color: red }');
        $this->church->refresh();

        $css = $this->church->custom_css;
        $this->assertStringNotContainsString('@import', $css);
    }

    public function test_custom_css_returns_null_when_empty(): void
    {
        $this->assertNull($this->church->custom_css);
    }
}
