<?php

namespace Tests\Unit\Models;

use App\Models\Church;
use App\Models\Donation;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonationTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        $this->church = Church::factory()->create();
    }

    // ==================
    // donor_display_name
    // ==================

    public function test_donor_display_name_for_person(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'first_name' => 'Іван',
            'last_name' => 'Петренко',
        ]);

        $donation = Donation::factory()->forChurch($this->church)->create([
            'person_id' => $person->id,
            'is_anonymous' => false,
        ]);

        $this->assertEquals('Іван Петренко', $donation->donor_display_name);
    }

    public function test_donor_display_name_anonymous(): void
    {
        $donation = Donation::factory()->forChurch($this->church)->anonymous()->create();

        $this->assertEquals('Анонімний донор', $donation->donor_display_name);
    }

    public function test_donor_display_name_from_donor_name(): void
    {
        $donation = Donation::factory()->forChurch($this->church)->create([
            'person_id' => null,
            'donor_name' => 'Зовнішній Донор',
            'is_anonymous' => false,
        ]);

        $this->assertEquals('Зовнішній Донор', $donation->donor_display_name);
    }

    public function test_donor_display_name_unknown(): void
    {
        $donation = Donation::factory()->forChurch($this->church)->create([
            'person_id' => null,
            'donor_name' => null,
            'is_anonymous' => false,
        ]);

        $this->assertEquals('Невідомий', $donation->donor_display_name);
    }

    // ==================
    // Formatted Amount
    // ==================

    public function test_formatted_amount(): void
    {
        $donation = Donation::factory()->forChurch($this->church)->create([
            'amount' => 1500.50,
            'currency' => 'UAH',
        ]);

        $this->assertEquals('1 500,50 UAH', $donation->formatted_amount);
    }

    // ==================
    // Status Accessors
    // ==================

    public function test_status_color(): void
    {
        $donation = Donation::factory()->forChurch($this->church)->create(['status' => 'pending']);
        $this->assertEquals('yellow', $donation->status_color);

        $donation->update(['status' => 'completed']);
        $this->assertEquals('green', $donation->status_color);

        $donation->update(['status' => 'failed']);
        $this->assertEquals('red', $donation->status_color);
    }

    public function test_status_label(): void
    {
        $donation = Donation::factory()->forChurch($this->church)->create(['status' => 'completed']);
        $this->assertEquals('Завершено', $donation->status_label);
    }

    // ==================
    // Scopes
    // ==================

    public function test_completed_scope(): void
    {
        Donation::factory()->forChurch($this->church)->completed()->create();
        Donation::factory()->forChurch($this->church)->pending()->create();

        $this->assertCount(1, Donation::completed()->get());
    }

    public function test_this_month_scope(): void
    {
        Donation::factory()->forChurch($this->church)->create(['created_at' => now()]);
        Donation::factory()->forChurch($this->church)->create(['created_at' => now()->subMonths(2)]);

        $this->assertCount(1, Donation::thisMonth()->get());
    }
}
