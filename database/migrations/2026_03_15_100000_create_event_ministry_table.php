<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_ministry', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('ministry_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['event_id', 'ministry_id']);
        });

        // Backfill from existing event_ministry_team assignments
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('
                INSERT IGNORE INTO event_ministry (event_id, ministry_id, created_at, updated_at)
                SELECT DISTINCT event_id, ministry_id, MIN(created_at), NOW()
                FROM event_ministry_team
                GROUP BY event_id, ministry_id
            ');

            // Also backfill from events.ministry_id
            DB::statement('
                INSERT IGNORE INTO event_ministry (event_id, ministry_id, created_at, updated_at)
                SELECT id, ministry_id, created_at, NOW()
                FROM events
                WHERE ministry_id IS NOT NULL AND deleted_at IS NULL
            ');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('event_ministry');
    }
};
