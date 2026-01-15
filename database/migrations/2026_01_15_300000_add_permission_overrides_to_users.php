<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // JSON field to store individual permission overrides
            // Format: {"module": {"allow": ["action1"], "deny": ["action2"]}}
            $table->json('permission_overrides')->nullable()->after('church_role_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('permission_overrides');
        });
    }
};
