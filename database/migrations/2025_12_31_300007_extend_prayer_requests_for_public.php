<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prayer_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('prayer_requests', 'submitter_name')) {
                $table->string('submitter_name')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('prayer_requests', 'submitter_email')) {
                $table->string('submitter_email')->nullable()->after('submitter_name');
            }
            if (!Schema::hasColumn('prayer_requests', 'is_from_public')) {
                $table->boolean('is_from_public')->default(false)->after('submitter_email');
            }
            if (!Schema::hasColumn('prayer_requests', 'notify_on_prayer')) {
                $table->boolean('notify_on_prayer')->default(true)->after('is_from_public');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prayer_requests', function (Blueprint $table) {
            $columns = ['submitter_name', 'submitter_email', 'is_from_public', 'notify_on_prayer'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('prayer_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
