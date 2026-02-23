<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('attendances', 'total_members')) {
            Schema::table('attendances', function (Blueprint $table) {
                $table->unsignedInteger('total_members')->nullable()->after('members_present');
            });
        }

        // Backfill existing group attendances with current member count using raw SQL
        DB::statement("
            UPDATE attendances a
            SET a.total_members = (
                SELECT COUNT(*) FROM group_person gp WHERE gp.group_id = a.attendable_id
            )
            WHERE a.type = 'group'
              AND a.attendable_type = 'App\\\\Models\\\\Group'
              AND a.attendable_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('total_members');
        });
    }
};
