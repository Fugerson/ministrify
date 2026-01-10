<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_plan_items', function (Blueprint $table) {
            $table->json('responsible_statuses')->nullable()->after('responsible_names');
        });
    }

    public function down(): void
    {
        Schema::table('service_plan_items', function (Blueprint $table) {
            $table->dropColumn('responsible_statuses');
        });
    }
};
