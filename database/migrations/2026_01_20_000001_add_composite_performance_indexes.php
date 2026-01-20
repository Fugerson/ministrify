<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Check if index exists
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = \DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Audit logs - church + date composite for filtering
        Schema::table('audit_logs', function (Blueprint $table) {
            if (!$this->indexExists('audit_logs', 'audit_logs_church_id_created_at_index')) {
                $table->index(['church_id', 'created_at'], 'audit_logs_church_id_created_at_index');
            }
        });

        // Ministry-person pivot - composite for efficient joins
        Schema::table('ministry_person', function (Blueprint $table) {
            if (!$this->indexExists('ministry_person', 'ministry_person_person_ministry_index')) {
                $table->index(['person_id', 'ministry_id'], 'ministry_person_person_ministry_index');
            }
            if (!$this->indexExists('ministry_person', 'ministry_person_created_at_index')) {
                $table->index('created_at', 'ministry_person_created_at_index');
            }
        });

        // Group-person pivot - composite for efficient joins
        Schema::table('group_person', function (Blueprint $table) {
            if (!$this->indexExists('group_person', 'group_person_person_group_index')) {
                $table->index(['person_id', 'group_id'], 'group_person_person_group_index');
            }
        });

        // People - church + date composite for dashboard queries
        Schema::table('people', function (Blueprint $table) {
            if (!$this->indexExists('people', 'people_church_id_created_at_index')) {
                $table->index(['church_id', 'created_at'], 'people_church_id_created_at_index');
            }
            if (!$this->indexExists('people', 'people_church_id_joined_date_index')) {
                $table->index(['church_id', 'joined_date'], 'people_church_id_joined_date_index');
            }
        });

        // Transactions - church + date composite for financial queries
        Schema::table('transactions', function (Blueprint $table) {
            if (!$this->indexExists('transactions', 'transactions_church_id_date_index')) {
                $table->index(['church_id', 'date'], 'transactions_church_id_date_index');
            }
            if (!$this->indexExists('transactions', 'transactions_church_id_direction_date_index')) {
                $table->index(['church_id', 'direction', 'date'], 'transactions_church_id_direction_date_index');
            }
        });

        // Events - church + date composite for calendar queries
        Schema::table('events', function (Blueprint $table) {
            if (!$this->indexExists('events', 'events_church_id_date_index')) {
                $table->index(['church_id', 'date'], 'events_church_id_date_index');
            }
        });

        // Attendances - church + date composite
        Schema::table('attendances', function (Blueprint $table) {
            if (!$this->indexExists('attendances', 'attendances_church_id_date_index')) {
                $table->index(['church_id', 'date'], 'attendances_church_id_date_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('audit_logs_church_id_created_at_index');
        });

        Schema::table('ministry_person', function (Blueprint $table) {
            $table->dropIndex('ministry_person_person_ministry_index');
            $table->dropIndex('ministry_person_created_at_index');
        });

        Schema::table('group_person', function (Blueprint $table) {
            $table->dropIndex('group_person_person_group_index');
        });

        Schema::table('people', function (Blueprint $table) {
            $table->dropIndex('people_church_id_created_at_index');
            $table->dropIndex('people_church_id_joined_date_index');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_church_id_date_index');
            $table->dropIndex('transactions_church_id_direction_date_index');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('events_church_id_date_index');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('attendances_church_id_date_index');
        });
    }
};
