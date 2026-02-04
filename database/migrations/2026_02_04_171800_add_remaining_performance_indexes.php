<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add remaining performance indexes identified in project audit.
     */
    public function up(): void
    {
        // Users: soft delete queries with church scope
        Schema::table('users', function (Blueprint $table) {
            if (!$this->indexExists('users', 'users_church_id_deleted_at_index')) {
                $table->index(['church_id', 'deleted_at'], 'users_church_id_deleted_at_index');
            }
        });

        // People: soft delete queries with church scope
        Schema::table('people', function (Blueprint $table) {
            if (!$this->indexExists('people', 'people_church_id_deleted_at_index')) {
                $table->index(['church_id', 'deleted_at'], 'people_church_id_deleted_at_index');
            }
        });

        // Events: soft delete queries with church scope
        Schema::table('events', function (Blueprint $table) {
            if (!$this->indexExists('events', 'events_church_id_deleted_at_index')) {
                $table->index(['church_id', 'deleted_at'], 'events_church_id_deleted_at_index');
            }
        });

        // Transactions: comprehensive financial queries (church + direction + status + date)
        Schema::table('transactions', function (Blueprint $table) {
            if (!$this->indexExists('transactions', 'transactions_church_direction_status_date_index')) {
                $table->index(
                    ['church_id', 'direction', 'status', 'date'],
                    'transactions_church_direction_status_date_index'
                );
            }
        });

        // Attendance: polymorphic queries with date
        Schema::table('attendances', function (Blueprint $table) {
            if (!$this->indexExists('attendances', 'attendances_church_attendable_date_index')) {
                $table->index(
                    ['church_id', 'attendable_type', 'attendable_id', 'date'],
                    'attendances_church_attendable_date_index'
                );
            }
        });

        // Assignments: unique lookup by event + person
        Schema::table('assignments', function (Blueprint $table) {
            if (!$this->indexExists('assignments', 'assignments_event_id_person_id_index')) {
                $table->index(['event_id', 'person_id'], 'assignments_event_id_person_id_index');
            }
        });

        // Ministries: soft delete queries
        Schema::table('ministries', function (Blueprint $table) {
            if (!$this->indexExists('ministries', 'ministries_church_id_deleted_at_index')) {
                $table->index(['church_id', 'deleted_at'], 'ministries_church_id_deleted_at_index');
            }
        });

        // Groups: soft delete queries
        Schema::table('groups', function (Blueprint $table) {
            if (!$this->indexExists('groups', 'groups_church_id_deleted_at_index')) {
                $table->index(['church_id', 'deleted_at'], 'groups_church_id_deleted_at_index');
            }
        });

        // Attendance records: quick lookup by attendance + person
        Schema::table('attendance_records', function (Blueprint $table) {
            if (!$this->indexExists('attendance_records', 'attendance_records_attendance_person_index')) {
                $table->index(['attendance_id', 'person_id'], 'attendance_records_attendance_person_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_church_id_deleted_at_index');
        });

        Schema::table('people', function (Blueprint $table) {
            $table->dropIndex('people_church_id_deleted_at_index');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('events_church_id_deleted_at_index');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('transactions_church_direction_status_date_index');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('attendances_church_attendable_date_index');
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropIndex('assignments_event_id_person_id_index');
        });

        Schema::table('ministries', function (Blueprint $table) {
            $table->dropIndex('ministries_church_id_deleted_at_index');
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropIndex('groups_church_id_deleted_at_index');
        });

        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropIndex('attendance_records_attendance_person_index');
        });
    }

    /**
     * Check if index exists on table
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = Schema::getIndexes($table);
        foreach ($indexes as $index) {
            if ($index['name'] === $indexName) {
                return true;
            }
        }
        return false;
    }
};
