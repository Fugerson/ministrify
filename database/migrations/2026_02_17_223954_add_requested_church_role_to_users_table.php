<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Store the requested church role before it's approved
            $table->foreignId('requested_church_role_id')->nullable()->after('church_role_id')->constrained('church_roles')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeignKeyIfExists(['requested_church_role_id']);
                $table->dropColumn('requested_church_role_id');
            });
        } else {
            // SQLite doesn't support dropForeignKeyIfExists
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('requested_church_role_id');
            });
        }
    }
};
