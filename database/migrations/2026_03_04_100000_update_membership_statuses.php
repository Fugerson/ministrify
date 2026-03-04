<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Rename old 'active' status to 'leadership'
        DB::table('people')
            ->where('membership_status', 'active')
            ->update(['membership_status' => 'leadership']);
    }

    public function down(): void
    {
        // Revert: rename 'leadership' back to 'active', drop new statuses
        DB::table('people')
            ->where('membership_status', 'leadership')
            ->update(['membership_status' => 'active']);

        DB::table('people')
            ->whereIn('membership_status', ['servant', 'leader'])
            ->update(['membership_status' => 'member']);
    }
};
