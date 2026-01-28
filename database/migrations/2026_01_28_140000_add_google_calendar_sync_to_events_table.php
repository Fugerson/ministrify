<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('google_event_id')->nullable()->after('parent_event_id');
            $table->string('google_calendar_id')->nullable()->after('google_event_id');
            $table->timestamp('google_synced_at')->nullable()->after('google_calendar_id');
            $table->string('google_sync_status')->nullable()->after('google_synced_at'); // pending, synced, failed

            $table->index('google_event_id');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['google_event_id']);
            $table->dropColumn(['google_event_id', 'google_calendar_id', 'google_synced_at', 'google_sync_status']);
        });
    }
};
