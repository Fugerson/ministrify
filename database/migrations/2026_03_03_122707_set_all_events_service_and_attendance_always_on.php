<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Enable plan + attendance + music on all existing events
        DB::table('events')->where('is_service', false)->update(['is_service' => true]);
        DB::table('events')->where('track_attendance', false)->update(['track_attendance' => true]);
        DB::table('events')->whereNull('service_type')->update(['service_type' => 'sunday_service']);
    }

    public function down(): void
    {
        // Cannot reliably revert — original values unknown
    }
};
