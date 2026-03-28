<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Church;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditLogControllerTest extends TestCase
{
    use RefreshDatabase;

    private Church $church;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        [$this->church, $this->admin] = $this->createChurchWithAdmin();
    }

    private function createAuditLog(array $overrides = []): AuditLog
    {
        return AuditLog::create(array_merge([
            'church_id' => $this->church->id,
            'user_id' => $this->admin->id,
            'user_name' => $this->admin->name,
            'action' => 'created',
            'model_type' => 'App\\Models\\Person',
            'model_id' => 1,
            'model_name' => 'John Doe',
        ], $overrides));
    }

    // ==================
    // Index
    // ==================

    public function test_admin_can_view_audit_logs(): void
    {
        $this->createAuditLog();

        $response = $this->actingAs($this->admin)->get('/settings/audit-logs');

        $response->assertStatus(200);
        $response->assertViewHas('logs');
    }

    public function test_volunteer_cannot_view_audit_logs(): void
    {
        $volunteer = $this->createUserWithRole($this->church, 'volunteer');

        $response = $this->actingAs($volunteer)->get('/settings/audit-logs');

        $response->assertStatus(403);
    }

    public function test_guest_cannot_view_audit_logs(): void
    {
        $response = $this->get('/settings/audit-logs');

        $response->assertRedirect('/login');
    }

    // ==================
    // Filters
    // ==================

    public function test_filter_by_action(): void
    {
        $this->createAuditLog(['action' => 'created']);
        $this->createAuditLog(['action' => 'updated']);
        $this->createAuditLog(['action' => 'deleted']);

        $response = $this->actingAs($this->admin)->get('/settings/audit-logs?action=created');

        $response->assertStatus(200);
        $response->assertViewHas('logs', function ($logs) {
            return $logs->every(fn ($log) => $log->action === 'created');
        });
    }

    public function test_filter_by_model_type(): void
    {
        $this->createAuditLog(['model_type' => 'App\\Models\\Person']);
        $this->createAuditLog(['model_type' => 'App\\Models\\Event']);

        $response = $this->actingAs($this->admin)->get('/settings/audit-logs?model=Person');

        $response->assertStatus(200);
        $response->assertViewHas('logs', function ($logs) {
            return $logs->every(fn ($log) => $log->model_type === 'App\\Models\\Person');
        });
    }

    public function test_filter_by_user(): void
    {
        $otherAdmin = User::factory()->admin()->create([
            'church_id' => $this->church->id,
        ]);

        $this->createAuditLog(['user_id' => $this->admin->id, 'user_name' => $this->admin->name]);
        $this->createAuditLog(['user_id' => $otherAdmin->id, 'user_name' => $otherAdmin->name]);

        $response = $this->actingAs($this->admin)->get("/settings/audit-logs?user={$this->admin->id}");

        $response->assertStatus(200);
        $response->assertViewHas('logs', function ($logs) {
            return $logs->every(fn ($log) => $log->user_id === $this->admin->id);
        });
    }

    public function test_filter_by_date_range(): void
    {
        $oldLog = $this->createAuditLog(['model_name' => 'Old Entry']);
        // Use DB::table to bypass model events/timestamps and set created_at directly
        DB::table('audit_logs')
            ->where('id', $oldLog->id)
            ->update(['created_at' => now()->subDays(30)]);

        $recentLog = $this->createAuditLog(['model_name' => 'Recent Entry']);

        $from = now()->subDays(7)->format('Y-m-d');
        $to = now()->format('Y-m-d');

        $response = $this->actingAs($this->admin)->get("/settings/audit-logs?from={$from}&to={$to}");

        $response->assertStatus(200);
        $response->assertViewHas('logs', function ($logs) {
            // Only the recent entry should be present
            return $logs->contains(fn ($log) => $log->model_name === 'Recent Entry')
                && ! $logs->contains(fn ($log) => $log->model_name === 'Old Entry');
        });
    }

    public function test_filter_by_search_on_model_name(): void
    {
        $this->createAuditLog(['model_name' => 'John Doe']);
        $this->createAuditLog(['model_name' => 'Jane Smith']);

        $response = $this->actingAs($this->admin)->get('/settings/audit-logs?search=John');

        $response->assertStatus(200);
        $response->assertViewHas('logs', function ($logs) {
            return $logs->every(fn ($log) => str_contains($log->model_name, 'John'));
        });
    }

    public function test_filter_by_search_on_user_name(): void
    {
        $this->createAuditLog(['user_name' => 'Admin User', 'model_name' => 'Test']);
        $this->createAuditLog(['user_name' => 'Another User', 'model_name' => 'Test2']);

        $response = $this->actingAs($this->admin)->get('/settings/audit-logs?search=Admin');

        $response->assertStatus(200);
        $response->assertViewHas('logs', function ($logs) {
            return $logs->every(fn ($log) => str_contains($log->user_name, 'Admin'));
        });
    }

    // ==================
    // Show (JSON)
    // ==================

    public function test_show_returns_json(): void
    {
        $log = $this->createAuditLog([
            'old_values' => ['name' => 'Old Name'],
            'new_values' => ['name' => 'New Name'],
        ]);

        $response = $this->actingAs($this->admin)->getJson("/settings/audit-logs/{$log->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure(['log', 'changes']);
        $response->assertJsonPath('log.id', $log->id);
    }

    public function test_show_returns_changes_summary(): void
    {
        $log = $this->createAuditLog([
            'old_values' => ['first_name' => 'Old'],
            'new_values' => ['first_name' => 'New'],
        ]);

        $response = $this->actingAs($this->admin)->getJson("/settings/audit-logs/{$log->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure(['changes']);
    }

    // ==================
    // Multi-tenancy
    // ==================

    public function test_cannot_view_audit_log_from_another_church(): void
    {
        $otherChurch = Church::factory()->create();
        $otherAdmin = User::factory()->admin()->create([
            'church_id' => $otherChurch->id,
        ]);

        $otherLog = AuditLog::create([
            'church_id' => $otherChurch->id,
            'user_id' => $otherAdmin->id,
            'user_name' => $otherAdmin->name,
            'action' => 'created',
            'model_type' => 'App\\Models\\Person',
            'model_id' => 99,
            'model_name' => 'Secret Person',
        ]);

        $response = $this->actingAs($this->admin)->getJson("/settings/audit-logs/{$otherLog->id}");

        $response->assertStatus(404);
    }

    public function test_index_only_shows_logs_for_own_church(): void
    {
        // Create log for own church
        $this->createAuditLog(['model_name' => 'Own Church Log']);

        // Create log for another church (use a real user to avoid FK constraint on SQLite)
        $otherChurch = Church::factory()->create();
        $otherAdmin = User::factory()->admin()->create([
            'church_id' => $otherChurch->id,
        ]);
        AuditLog::create([
            'church_id' => $otherChurch->id,
            'user_id' => $otherAdmin->id,
            'user_name' => 'Other Admin',
            'action' => 'created',
            'model_type' => 'App\\Models\\Person',
            'model_id' => 99,
            'model_name' => 'Other Church Log',
        ]);

        $response = $this->actingAs($this->admin)->get('/settings/audit-logs');

        $response->assertStatus(200);
        $response->assertViewHas('logs', function ($logs) {
            return $logs->every(fn ($log) => $log->church_id === $this->church->id);
        });
    }

    // ==================
    // Pagination
    // ==================

    public function test_audit_logs_are_paginated(): void
    {
        // Create 60 logs (pagination is 50 per page)
        for ($i = 0; $i < 60; $i++) {
            $this->createAuditLog(['model_name' => "Person {$i}"]);
        }

        $response = $this->actingAs($this->admin)->get('/settings/audit-logs');

        $response->assertStatus(200);
        $response->assertViewHas('logs', function ($logs) {
            return $logs->count() === 50 && $logs->hasPages();
        });
    }

    public function test_audit_logs_second_page(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $this->createAuditLog(['model_name' => "Person {$i}"]);
        }

        $response = $this->actingAs($this->admin)->get('/settings/audit-logs?page=2');

        $response->assertStatus(200);
        $response->assertViewHas('logs', function ($logs) {
            return $logs->count() === 10;
        });
    }
}
