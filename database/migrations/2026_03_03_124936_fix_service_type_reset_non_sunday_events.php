<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Previous migration incorrectly set ALL events to service_type='sunday_service'.
        // Reset non-Sunday events back to NULL; keep only actual Sunday events as sunday_service.
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't have DAYOFWEEK — use strftime instead
            DB::table('events')
                ->where('service_type', 'sunday_service')
                ->whereRaw("strftime('%w', date) != '0'") // 0 = Sunday in SQLite
                ->update(['service_type' => null]);
        } else {
            DB::table('events')
                ->where('service_type', 'sunday_service')
                ->whereRaw('DAYOFWEEK(date) != 1') // 1 = Sunday in MySQL
                ->update(['service_type' => null]);
        }
    }

    public function down(): void
    {
        // Cannot reliably revert
    }
};
