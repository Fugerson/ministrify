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

            // Make type nullable (not required)
            $table->string('type')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('service_plan_items', function (Blueprint $table) {
            $table->dropColumn('responsible_names');
            $table->enum('type', [
                'worship', 'sermon', 'announcement', 'prayer', 'offering',
                'testimony', 'baptism', 'communion', 'child_blessing', 'special', 'other'
            ])->default('other')->change();
        });
    }
};
