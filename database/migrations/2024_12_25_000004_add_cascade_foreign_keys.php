<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds cascade delete foreign keys to prevent orphaned records.
     */
    public function up(): void
    {
        // Disable foreign key checks temporarily
        Schema::disableForeignKeyConstraints();

        // Events -> Church (cascade delete)
        $this->addForeignKeyIfNotExists('events', 'church_id', 'churches', 'id', 'cascade');

        // Ministries -> Church (cascade delete)
        $this->addForeignKeyIfNotExists('ministries', 'church_id', 'churches', 'id', 'cascade');

        // Groups -> Church (cascade delete)
        $this->addForeignKeyIfNotExists('groups', 'church_id', 'churches', 'id', 'cascade');

        // People -> Church (cascade delete)
        $this->addForeignKeyIfNotExists('people', 'church_id', 'churches', 'id', 'cascade');

        // Transactions -> Church (cascade delete)
        $this->addForeignKeyIfNotExists('transactions', 'church_id', 'churches', 'id', 'cascade');

        // Assignments -> Event (set null on delete)
        $this->addForeignKeyIfNotExists('assignments', 'event_id', 'events', 'id', 'cascade');

        // Assignments -> Person (set null on delete)
        $this->addForeignKeyIfNotExists('assignments', 'person_id', 'people', 'id', 'set null');

        // Attendances -> Church (cascade delete)
        $this->addForeignKeyIfNotExists('attendances', 'church_id', 'churches', 'id', 'cascade');

        // ServicePlanItems -> Event (cascade delete when event is deleted)
        $this->addForeignKeyIfNotExists('service_plan_items', 'event_id', 'events', 'id', 'cascade');

        // Positions -> Ministry (cascade delete when ministry is deleted)
        $this->addForeignKeyIfNotExists('positions', 'ministry_id', 'ministries', 'id', 'cascade');

        // Assignments -> Position (set null when position is deleted)
        $this->addForeignKeyIfNotExists('assignments', 'position_id', 'positions', 'id', 'set null');

        // MinistryMeetings -> Ministry (cascade delete)
        $this->addForeignKeyIfNotExists('ministry_meetings', 'ministry_id', 'ministries', 'id', 'cascade');

        // MinistryJoinRequests -> Ministry (cascade delete)
        $this->addForeignKeyIfNotExists('ministry_join_requests', 'ministry_id', 'ministries', 'id', 'cascade');

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        $this->dropForeignKeyIfExists('events', 'events_church_id_foreign');
        $this->dropForeignKeyIfExists('ministries', 'ministries_church_id_foreign');
        $this->dropForeignKeyIfExists('groups', 'groups_church_id_foreign');
        $this->dropForeignKeyIfExists('people', 'people_church_id_foreign');
        $this->dropForeignKeyIfExists('transactions', 'transactions_church_id_foreign');
        $this->dropForeignKeyIfExists('assignments', 'assignments_event_id_foreign');
        $this->dropForeignKeyIfExists('assignments', 'assignments_person_id_foreign');
        $this->dropForeignKeyIfExists('attendances', 'attendances_church_id_foreign');
        $this->dropForeignKeyIfExists('service_plan_items', 'service_plan_items_event_id_foreign');
        $this->dropForeignKeyIfExists('positions', 'positions_ministry_id_foreign');
        $this->dropForeignKeyIfExists('assignments', 'assignments_position_id_foreign');
        $this->dropForeignKeyIfExists('ministry_meetings', 'ministry_meetings_ministry_id_foreign');
        $this->dropForeignKeyIfExists('ministry_join_requests', 'ministry_join_requests_ministry_id_foreign');

        Schema::enableForeignKeyConstraints();
    }

    private function addForeignKeyIfNotExists(
        string $table,
        string $column,
        string $referencedTable,
        string $referencedColumn,
        string $onDelete = 'cascade'
    ): void {
        $foreignKeyName = "{$table}_{$column}_foreign";

        // Check if foreign key already exists
        $exists = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_TYPE = 'FOREIGN KEY'
            AND TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND CONSTRAINT_NAME = ?
        ", [$table, $foreignKeyName]);

        if (empty($exists)) {
            try {
                Schema::table($table, function (Blueprint $t) use ($column, $referencedTable, $referencedColumn, $onDelete) {
                    $t->foreign($column)
                        ->references($referencedColumn)
                        ->on($referencedTable)
                        ->onDelete($onDelete);
                });
            } catch (\Exception $e) {
                // Log but don't fail - foreign key might already exist with different name
                \Log::warning("Could not add foreign key on {$table}.{$column}: " . $e->getMessage());
            }
        }
    }

    private function dropForeignKeyIfExists(string $table, string $foreignKeyName): void
    {
        try {
            Schema::table($table, function (Blueprint $t) use ($foreignKeyName) {
                $t->dropForeign($foreignKeyName);
            });
        } catch (\Exception $e) {
            // Key might not exist
        }
    }
};
