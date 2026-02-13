<?php

namespace Tests\Unit\Models;

use App\Models\AuditLog;
use App\Models\Church;
use App\Models\Person;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    protected function setUp(): void
    {
        parent::setUp();
        $this->church = Church::factory()->create();
    }

    // ==================
    // Action Labels
    // ==================

    public function test_action_label_for_created(): void
    {
        $log = new AuditLog(['action' => 'created']);
        $this->assertEquals('Створено', $log->action_label);
    }

    public function test_action_label_for_updated(): void
    {
        $log = new AuditLog(['action' => 'updated']);
        $this->assertEquals('Оновлено', $log->action_label);
    }

    public function test_action_label_for_deleted(): void
    {
        $log = new AuditLog(['action' => 'deleted']);
        $this->assertEquals('Видалено', $log->action_label);
    }

    public function test_action_label_for_restored(): void
    {
        $log = new AuditLog(['action' => 'restored']);
        $this->assertEquals('Відновлено', $log->action_label);
    }

    public function test_action_label_for_login(): void
    {
        $log = new AuditLog(['action' => 'login']);
        $this->assertEquals('Вхід', $log->action_label);
    }

    public function test_action_label_for_unknown(): void
    {
        $log = new AuditLog(['action' => 'nonexistent']);
        $this->assertNotNull($log->action_label);
    }

    // ==================
    // Model Labels
    // ==================

    public function test_model_label_for_person(): void
    {
        $log = new AuditLog(['model_type' => 'App\\Models\\Person']);
        $this->assertEquals('Член церкви', $log->model_label);
    }

    public function test_model_label_for_event(): void
    {
        $log = new AuditLog(['model_type' => 'App\\Models\\Event']);
        $this->assertEquals('Подія', $log->model_label);
    }

    public function test_model_label_for_ministry(): void
    {
        $log = new AuditLog(['model_type' => 'App\\Models\\Ministry']);
        $this->assertEquals('Служіння', $log->model_label);
    }

    public function test_model_label_for_transaction(): void
    {
        $log = new AuditLog(['model_type' => 'App\\Models\\Transaction']);
        $this->assertEquals('Транзакція', $log->model_label);
    }

    public function test_model_label_for_church(): void
    {
        $log = new AuditLog(['model_type' => 'App\\Models\\Church']);
        $this->assertEquals('Церква', $log->model_label);
    }

    // ==================
    // Description
    // ==================

    public function test_description_combines_action_and_model(): void
    {
        $log = new AuditLog([
            'action' => 'created',
            'model_type' => 'App\\Models\\Person',
        ]);

        $description = $log->description;
        $this->assertStringContainsString('Створено', $description);
    }

    public function test_description_for_login(): void
    {
        $log = new AuditLog(['action' => 'login']);
        $this->assertStringContainsString('Вхід', $log->description);
    }

    // ==================
    // Changes Summary
    // ==================

    public function test_get_changes_summary_returns_array(): void
    {
        $log = AuditLog::create([
            'church_id' => $this->church->id,
            'action' => 'updated',
            'model_type' => 'App\\Models\\Person',
            'model_id' => 1,
            'old_values' => ['first_name' => 'Іван'],
            'new_values' => ['first_name' => 'Петро'],
        ]);

        $summary = $log->changes_summary;
        $this->assertIsArray($summary);
        $this->assertNotEmpty($summary);
    }

    public function test_changes_summary_text_truncates(): void
    {
        $log = AuditLog::create([
            'church_id' => $this->church->id,
            'action' => 'updated',
            'model_type' => 'App\\Models\\Person',
            'model_id' => 1,
            'old_values' => [
                'first_name' => 'Іван',
                'last_name' => 'Петренко',
                'email' => 'old@test.com',
                'phone' => '+380991111111',
            ],
            'new_values' => [
                'first_name' => 'Петро',
                'last_name' => 'Сидоренко',
                'email' => 'new@test.com',
                'phone' => '+380992222222',
            ],
        ]);

        $text = $log->changesSummaryText;
        $this->assertIsString($text);
    }

    // ==================
    // Auditable Whitelist
    // ==================

    public function test_auditable_returns_model_for_allowed_type(): void
    {
        $person = Person::factory()->forChurch($this->church)->create();

        $log = AuditLog::create([
            'church_id' => $this->church->id,
            'action' => 'updated',
            'model_type' => 'App\\Models\\Person',
            'model_id' => $person->id,
            'old_values' => [],
            'new_values' => [],
        ]);

        $model = $log->auditable();
        $this->assertNotNull($model);
    }

    public function test_auditable_returns_null_for_invalid_type(): void
    {
        $log = AuditLog::create([
            'church_id' => $this->church->id,
            'action' => 'updated',
            'model_type' => 'App\\Models\\MaliciousModel',
            'model_id' => 1,
            'old_values' => [],
            'new_values' => [],
        ]);

        $model = $log->auditable();
        $this->assertNull($model);
    }

    // ==================
    // Scopes
    // ==================

    public function test_for_church_scope(): void
    {
        $otherChurch = Church::factory()->create();

        AuditLog::create([
            'church_id' => $this->church->id,
            'action' => 'created',
            'model_type' => 'App\\Models\\Person',
            'model_id' => 1,
        ]);
        AuditLog::create([
            'church_id' => $otherChurch->id,
            'action' => 'created',
            'model_type' => 'App\\Models\\Person',
            'model_id' => 2,
        ]);

        $logs = AuditLog::forChurch($this->church->id)->get();
        $this->assertCount(1, $logs);
    }

    public function test_for_model_scope(): void
    {
        AuditLog::create([
            'church_id' => $this->church->id,
            'action' => 'created',
            'model_type' => 'App\\Models\\Person',
            'model_id' => 1,
        ]);
        AuditLog::create([
            'church_id' => $this->church->id,
            'action' => 'updated',
            'model_type' => 'App\\Models\\Event',
            'model_id' => 1,
        ]);

        $logs = AuditLog::forModel('App\\Models\\Person', 1)->get();
        $this->assertCount(1, $logs);
    }
}
