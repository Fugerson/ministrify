<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Previous migration incorrectly set ALL events to service_type='sunday_service'.
        // Reset non-Sunday events back to NULL; keep only actual Sunday events as sunday_service.
        DB::table('events')
            ->where('service_type', 'sunday_service')
            ->whereRaw('DAYOFWEEK(date) != 1') // 1 = Sunday in MySQL
            ->update(['service_type' => null]);
    }

    public function down(): void
    {
        // Cannot reliably revert
    }
};
