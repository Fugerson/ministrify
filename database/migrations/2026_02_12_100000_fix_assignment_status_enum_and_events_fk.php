<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            // 1. Add 'attended' to assignment status enum
            DB::statement("ALTER TABLE assignments MODIFY COLUMN status ENUM('pending', 'confirmed', 'declined', 'attended') DEFAULT 'pending'");

            // 2. Fix events.ministry_id ON DELETE CASCADE → SET NULL
            Schema::table('events', function ($table) {
                $table->dropForeign(['ministry_id']);
                $table->foreign('ministry_id')->references('id')->on('ministries')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE assignments MODIFY COLUMN status ENUM('pending', 'confirmed', 'declined') DEFAULT 'pending'");

            Schema::table('events', function ($table) {
                $table->dropForeign(['ministry_id']);
                $table->foreign('ministry_id')->references('id')->on('ministries')->cascadeOnDelete();
            });
        }
    }
};
