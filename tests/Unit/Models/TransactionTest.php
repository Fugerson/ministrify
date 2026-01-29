<?php

namespace Tests\Unit\Models;

use App\Models\Church;
use App\Models\Person;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        $this->church = Church::factory()->create();
    }

    // ==================
    // Attributes
    // ==================

    public function test_is_income_attribute(): void
    {
        $transaction = Transaction::factory()->forChurch($this->church)->income()->create();

        $this->assertTrue($transaction->is_income);
        $this->assertFalse($transaction->is_expense);
    }

    public function test_is_expense_attribute(): void
    {
        $transaction = Transaction::factory()->forChurch($this->church)->expense()->create();

        $this->assertTrue($transaction->is_expense);
        $this->assertFalse($transaction->is_income);
    }

    public function test_donor_display_name_anonymous(): void
    {
        $transaction = Transaction::factory()->forChurch($this->church)->income()->create([
            'is_anonymous' => true,
        ]);

        $this->assertEquals('Анонімно', $transaction->donor_display_name);
    }

    public function test_donor_display_name_with_person(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'first_name' => 'Іван',
            'last_name' => 'Петренко',
        ]);
        $transaction = Transaction::factory()->forChurch($this->church)->income()->create([
            'person_id' => $person->id,
            'is_anonymous' => false,
        ]);

        $this->assertEquals('Іван Петренко', $transaction->donor_display_name);
    }

    public function test_donor_display_name_with_donor_name(): void
    {
        $transaction = Transaction::factory()->forChurch($this->church)->income()->create([
            'donor_name' => 'Зовнішній Донор',
            'is_anonymous' => false,
            'person_id' => null,
        ]);

        $this->assertEquals('Зовнішній Донор', $transaction->donor_display_name);
    }

    public function test_donor_display_name_unknown(): void
    {
        $transaction = Transaction::factory()->forChurch($this->church)->income()->create([
            'is_anonymous' => false,
            'person_id' => null,
            'donor_name' => null,
        ]);

        $this->assertEquals('Не вказано', $transaction->donor_display_name);
    }

    // ==================
    // Status Methods
    // ==================

    public function test_mark_as_completed(): void
    {
        $transaction = Transaction::factory()->forChurch($this->church)->pending()->create();

        $transaction->markAsCompleted();
        $transaction->refresh();

        $this->assertEquals(Transaction::STATUS_COMPLETED, $transaction->status);
        $this->assertNotNull($transaction->paid_at);
    }

    public function test_mark_as_failed(): void
    {
        $transaction = Transaction::factory()->forChurch($this->church)->pending()->create();

        $transaction->markAsFailed();
        $transaction->refresh();

        $this->assertEquals(Transaction::STATUS_FAILED, $transaction->status);
    }

    public function test_refund(): void
    {
        $transaction = Transaction::factory()->forChurch($this->church)->completed()->create();

        $transaction->refund();
        $transaction->refresh();

        $this->assertEquals(Transaction::STATUS_REFUNDED, $transaction->status);
    }

    // ==================
    // Scopes
    // ==================

    public function test_incoming_scope(): void
    {
        Transaction::factory()->forChurch($this->church)->income()->create();
        Transaction::factory()->forChurch($this->church)->expense()->create();

        $incoming = Transaction::incoming()->get();
        $this->assertCount(1, $incoming);
        $this->assertEquals(Transaction::DIRECTION_IN, $incoming->first()->direction);
    }

    public function test_outgoing_scope(): void
    {
        Transaction::factory()->forChurch($this->church)->income()->create();
        Transaction::factory()->forChurch($this->church)->expense()->create();

        $outgoing = Transaction::outgoing()->get();
        $this->assertCount(1, $outgoing);
        $this->assertEquals(Transaction::DIRECTION_OUT, $outgoing->first()->direction);
    }

    public function test_completed_scope(): void
    {
        Transaction::factory()->forChurch($this->church)->completed()->create();
        Transaction::factory()->forChurch($this->church)->pending()->create();

        $completed = Transaction::completed()->get();
        $this->assertCount(1, $completed);
    }

    public function test_for_period_month_scope(): void
    {
        Transaction::factory()->forChurch($this->church)->create(['date' => now()]);
        Transaction::factory()->forChurch($this->church)->create(['date' => now()->subMonths(3)]);

        $results = Transaction::forPeriod('month')->get();
        $this->assertCount(1, $results);
    }

    public function test_for_period_year_scope(): void
    {
        Transaction::factory()->forChurch($this->church)->create(['date' => now()]);
        Transaction::factory()->forChurch($this->church)->create(['date' => now()->subYears(2)]);

        $results = Transaction::forPeriod('year')->get();
        $this->assertCount(1, $results);
    }

    // ==================
    // Labels
    // ==================

    public function test_source_type_label(): void
    {
        $transaction = Transaction::factory()->forChurch($this->church)->tithe()->create();

        $this->assertEquals('Десятина', $transaction->source_type_label);
    }

    public function test_status_label(): void
    {
        $transaction = Transaction::factory()->forChurch($this->church)->completed()->create();

        $this->assertEquals('Завершено', $transaction->status_label);
    }

    public function test_status_color(): void
    {
        $completed = Transaction::factory()->forChurch($this->church)->completed()->create();
        $this->assertEquals('green', $completed->status_color);

        $pending = Transaction::factory()->forChurch($this->church)->pending()->create();
        $this->assertEquals('yellow', $pending->status_color);
    }
}
