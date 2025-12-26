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
     * Migrates legacy event_id data to polymorphic attendable relationship.
     */
    public function up(): void
    {
        // Migrate legacy event_id records to polymorphic format
        DB::table('attendances')
            ->whereNotNull('event_id')
            ->whereNull('attendable_type')
            ->update([
                'attendable_type' => 'App\\Models\\Event',
                'attendable_id' => DB::raw('event_id'),
                'type' => DB::raw("COALESCE(type, 'service')"),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Copy polymorphic data back to event_id for Event types
        DB::table('attendances')
            ->where('attendable_type', 'App\\Models\\Event')
            ->whereNull('event_id')
            ->update([
                'event_id' => DB::raw('attendable_id'),
            ]);
    }
};
