<?php

namespace Tests\Unit\Models;

use App\Models\Church;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonTest extends TestCase
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

    public function test_full_name_attribute(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'first_name' => 'Іван',
            'last_name' => 'Петренко',
        ]);

        $this->assertEquals('Іван Петренко', $person->full_name);
    }

    public function test_age_attribute_with_birth_date(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'birth_date' => now()->subYears(25)->subDays(10),
        ]);

        $this->assertEquals(25, $person->age);
    }

    public function test_age_attribute_without_birth_date(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'birth_date' => null,
        ]);

        $this->assertNull($person->age);
    }

    public function test_age_category_child(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'birth_date' => now()->subYears(8),
        ]);

        $this->assertEquals('child', $person->age_category);
    }

    public function test_age_category_teen(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'birth_date' => now()->subYears(15),
        ]);

        $this->assertEquals('teen', $person->age_category);
    }

    public function test_age_category_youth(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'birth_date' => now()->subYears(25),
        ]);

        $this->assertEquals('youth', $person->age_category);
    }

    public function test_age_category_adult(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'birth_date' => now()->subYears(45),
        ]);

        $this->assertEquals('adult', $person->age_category);
    }

    public function test_age_category_senior(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'birth_date' => now()->subYears(65),
        ]);

        $this->assertEquals('senior', $person->age_category);
    }

    public function test_age_category_null_without_birth_date(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'birth_date' => null,
        ]);

        $this->assertNull($person->age_category);
    }

    public function test_is_baptized_with_date(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'baptism_date' => now()->subYear(),
        ]);

        $this->assertTrue($person->is_baptized);
    }

    public function test_is_baptized_without_date(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'baptism_date' => null,
        ]);

        $this->assertFalse($person->is_baptized);
    }

    public function test_is_married(): void
    {
        $married = Person::factory()->forChurch($this->church)->create([
            'marital_status' => Person::MARITAL_MARRIED,
        ]);
        $single = Person::factory()->forChurch($this->church)->create([
            'marital_status' => Person::MARITAL_SINGLE,
        ]);

        $this->assertTrue($married->is_married);
        $this->assertFalse($single->is_married);
    }

    public function test_membership_duration_with_joined_date(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'joined_date' => now()->subYears(2)->subMonths(3),
        ]);

        $duration = $person->membership_duration;
        $this->assertStringContainsString('2', $duration);
    }

    public function test_membership_duration_without_joined_date(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'joined_date' => null,
        ]);

        $this->assertEquals('Невідомо', $person->membership_duration);
    }

    // ==================
    // Scopes
    // ==================

    public function test_search_scope(): void
    {
        Person::factory()->forChurch($this->church)->create([
            'first_name' => 'Олександр',
            'last_name' => 'Коваль',
        ]);
        Person::factory()->forChurch($this->church)->create([
            'first_name' => 'Марія',
            'last_name' => 'Шевченко',
        ]);

        $results = Person::search('Олекс')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Олександр', $results->first()->first_name);
    }

    public function test_search_scope_by_email(): void
    {
        Person::factory()->forChurch($this->church)->create([
            'email' => 'test@example.com',
        ]);

        $results = Person::search('test@example')->get();
        $this->assertCount(1, $results);
    }

    public function test_with_tag_scope(): void
    {
        $tag = Tag::create(['name' => 'Новоприбулий', 'church_id' => $this->church->id]);
        $person = Person::factory()->forChurch($this->church)->create();
        $person->tags()->attach($tag);

        $otherPerson = Person::factory()->forChurch($this->church)->create();

        $results = Person::withTag($tag->id)->get();
        $this->assertCount(1, $results);
        $this->assertEquals($person->id, $results->first()->id);
    }

    public function test_in_ministry_scope(): void
    {
        $ministry = Ministry::factory()->forChurch($this->church)->create();
        $person = Person::factory()->forChurch($this->church)->create();
        $ministry->members()->attach($person);

        $otherPerson = Person::factory()->forChurch($this->church)->create();

        $results = Person::inMinistry($ministry->id)->get();
        $this->assertCount(1, $results);
        $this->assertEquals($person->id, $results->first()->id);
    }

    public function test_birthday_in_month_scope(): void
    {
        $currentMonth = now()->month;
        Person::factory()->forChurch($this->church)->create([
            'birth_date' => now()->subYears(25)->startOfMonth(),
        ]);
        Person::factory()->forChurch($this->church)->create([
            'birth_date' => now()->subYears(30)->addMonths(3),
        ]);

        $results = Person::birthdayInMonth($currentMonth)->get();
        $this->assertCount(1, $results);
    }

    public function test_gender_scope(): void
    {
        Person::factory()->forChurch($this->church)->create(['gender' => 'male']);
        Person::factory()->forChurch($this->church)->create(['gender' => 'female']);
        Person::factory()->forChurch($this->church)->create(['gender' => 'male']);

        $males = Person::gender('male')->get();
        $females = Person::gender('female')->get();

        $this->assertCount(2, $males);
        $this->assertCount(1, $females);
    }

    public function test_members_scope(): void
    {
        Person::factory()->forChurch($this->church)->create(['membership_status' => 'member']);
        Person::factory()->forChurch($this->church)->create(['membership_status' => 'active']);
        Person::factory()->forChurch($this->church)->create(['membership_status' => 'guest']);

        $members = Person::members()->get();
        $this->assertCount(2, $members);
    }

    public function test_baptized_scope(): void
    {
        Person::factory()->forChurch($this->church)->create(['baptism_date' => now()]);
        Person::factory()->forChurch($this->church)->create(['baptism_date' => null]);

        $baptized = Person::baptized()->get();
        $this->assertCount(1, $baptized);
    }

    public function test_shepherds_scope(): void
    {
        Person::factory()->forChurch($this->church)->shepherd()->create();
        Person::factory()->forChurch($this->church)->create();

        $shepherds = Person::shepherds()->get();
        $this->assertCount(1, $shepherds);
    }

    // ==================
    // Status Promotion
    // ==================

    public function test_promote_status(): void
    {
        $person = Person::factory()->forChurch($this->church)->guest()->create();

        $person->promoteStatus();
        $person->refresh();
        $this->assertEquals(Person::STATUS_NEWCOMER, $person->membership_status);

        $person->promoteStatus();
        $person->refresh();
        $this->assertEquals(Person::STATUS_MEMBER, $person->membership_status);

        $person->promoteStatus();
        $person->refresh();
        $this->assertEquals(Person::STATUS_ACTIVE, $person->membership_status);

        // Cannot promote beyond active
        $person->promoteStatus();
        $person->refresh();
        $this->assertEquals(Person::STATUS_ACTIVE, $person->membership_status);
    }

    // ==================
    // Labels
    // ==================

    public function test_church_role_label(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'church_role' => Person::ROLE_PASTOR,
        ]);

        $this->assertEquals('Пастор', $person->church_role_label);
    }

    public function test_membership_status_label(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'membership_status' => Person::STATUS_ACTIVE,
        ]);

        $this->assertEquals('Активний член', $person->membership_status_label);
    }

    public function test_gender_label(): void
    {
        $person = Person::factory()->forChurch($this->church)->create([
            'gender' => 'male',
        ]);

        $this->assertEquals('Чоловік', $person->gender_label);
    }
}
