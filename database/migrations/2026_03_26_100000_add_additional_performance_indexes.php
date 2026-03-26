<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Membership status is frequently filtered in people queries
        Schema::table('people', function (Blueprint $table) {
            $table->index('membership_status');
        });

        // Person + date for "who attended on date X" queries
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->index(['attendance_id', 'present']);
        });

        // Note: transactions_church_id_direction_date_index already exists from earlier migration
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('people', function (Blueprint $table) {
            $table->dropIndex(['membership_status']);
        });

        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropIndex(['attendance_id', 'present']);
        });

        // transactions_church_id_direction_date_index managed by earlier migration
    }
};
