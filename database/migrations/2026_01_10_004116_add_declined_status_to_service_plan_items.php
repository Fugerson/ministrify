<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }
        DB::statement("ALTER TABLE service_plan_items MODIFY COLUMN status ENUM('planned', 'confirmed', 'declined', 'completed') NOT NULL DEFAULT 'planned'");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }
        // First update any 'declined' to 'planned' to avoid data loss
        DB::table('service_plan_items')->where('status', 'declined')->update(['status' => 'planned']);

        DB::statement("ALTER TABLE service_plan_items MODIFY COLUMN status ENUM('planned', 'confirmed', 'completed') NOT NULL DEFAULT 'planned'");
    }
};
