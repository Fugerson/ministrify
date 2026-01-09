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
        // Transactions - frequently filtered columns
        Schema::table('transactions', function (Blueprint $table) {
            if (!$this->indexExists('transactions', 'transactions_date_index')) {
                $table->index('date');
            }
            if (!$this->indexExists('transactions', 'transactions_direction_index')) {
                $table->index('direction');
            }
            if (!$this->indexExists('transactions', 'transactions_status_index')) {
                $table->index('status');
            }
            if (!$this->indexExists('transactions', 'transactions_source_type_index')) {
                $table->index('source_type');
            }
        });

        // People - frequently filtered columns
        Schema::table('people', function (Blueprint $table) {
            if (!$this->indexExists('people', 'people_church_role_id_index')) {
                $table->index('church_role_id');
            }
            if (!$this->indexExists('people', 'people_shepherd_id_index')) {
                $table->index('shepherd_id');
            }
            if (!$this->indexExists('people', 'people_is_shepherd_index')) {
                $table->index('is_shepherd');
            }
        });

        // Events - frequently filtered columns
        Schema::table('events', function (Blueprint $table) {
            if (!$this->indexExists('events', 'events_is_service_index')) {
                $table->index('is_service');
            }
            if (!$this->indexExists('events', 'events_track_attendance_index')) {
                $table->index('track_attendance');
            }
        });

        // Attendances - type filter
        Schema::table('attendances', function (Blueprint $table) {
            if (!$this->indexExists('attendances', 'attendances_type_index')) {
                $table->index('type');
            }
        });

        // Board cards - frequently used filters
        Schema::table('board_cards', function (Blueprint $table) {
            if (!$this->indexExists('board_cards', 'board_cards_priority_index')) {
                $table->index('priority');
            }
            if (!$this->indexExists('board_cards', 'board_cards_due_date_index')) {
                $table->index('due_date');
            }
            if (!$this->indexExists('board_cards', 'board_cards_is_completed_index')) {
                $table->index('is_completed');
            }
        });

        // Audit logs - sorting and filtering
        Schema::table('audit_logs', function (Blueprint $table) {
            if (!$this->indexExists('audit_logs', 'audit_logs_action_index')) {
                $table->index('action');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['date']);
            $table->dropIndex(['direction']);
            $table->dropIndex(['status']);
            $table->dropIndex(['source_type']);
        });

        Schema::table('people', function (Blueprint $table) {
            $table->dropIndex(['church_role_id']);
            $table->dropIndex(['shepherd_id']);
            $table->dropIndex(['is_shepherd']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['is_service']);
            $table->dropIndex(['track_attendance']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['type']);
        });

        Schema::table('board_cards', function (Blueprint $table) {
            $table->dropIndex(['priority']);
            $table->dropIndex(['due_date']);
            $table->dropIndex(['is_completed']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['action']);
        });
    }
};
