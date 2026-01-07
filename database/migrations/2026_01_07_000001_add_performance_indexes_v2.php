<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Transactions - frequently filtered columns
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('date');
            $table->index('direction');
            $table->index('status');
            $table->index('source_type');
        });

        // People - frequently filtered columns
        Schema::table('people', function (Blueprint $table) {
            $table->index('church_role_id');
            $table->index('shepherd_id');
            $table->index('is_shepherd');
        });

        // Events - frequently filtered columns
        Schema::table('events', function (Blueprint $table) {
            $table->index('is_service');
            $table->index('track_attendance');
        });

        // Attendances - type filter
        Schema::table('attendances', function (Blueprint $table) {
            $table->index('type');
        });

        // Board cards - frequently used filters
        Schema::table('board_cards', function (Blueprint $table) {
            $table->index('priority');
            $table->index('due_date');
            $table->index('is_completed');
        });

        // Audit logs - sorting and filtering
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index('action');
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
