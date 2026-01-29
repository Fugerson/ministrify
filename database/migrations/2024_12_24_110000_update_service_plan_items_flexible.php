<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_plan_items', function (Blueprint $table) {
            // Add flexible responsible names field (for "Ненсі/Віка" format)
            $table->string('responsible_names')->nullable()->after('responsible_id');
        });

        // Column change requires Doctrine DBAL — skip on SQLite (testing)
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('service_plan_items', function (Blueprint $table) {
                $table->string('type')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('service_plan_items', function (Blueprint $table) {
            $table->dropColumn('responsible_names');
        });

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('service_plan_items', function (Blueprint $table) {
                $table->enum('type', [
                    'worship', 'sermon', 'announcement', 'prayer', 'offering',
                    'testimony', 'baptism', 'communion', 'child_blessing', 'special', 'other'
                ])->default('other')->change();
            });
        }
    }
};
