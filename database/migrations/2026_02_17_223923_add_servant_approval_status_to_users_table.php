<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds servant role approval system to prevent immediately granting
     * access when a user is assigned a servant role
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Status of servant role approval: null (no pending), 'pending', 'approved'
            $table->enum('servant_approval_status', ['pending', 'approved'])->nullable()->after('church_role_id');
            $table->timestamp('servant_approved_at')->nullable()->after('servant_approval_status');
        });

        // For church_user pivot table - also track approval
        if (Schema::hasTable('church_user')) {
            Schema::table('church_user', function (Blueprint $table) {
                $table->enum('role_approval_status', ['pending', 'approved'])->nullable()->default('pending')->after('church_role_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['servant_approval_status', 'servant_approved_at']);
        });

        if (Schema::hasTable('church_user')) {
            Schema::table('church_user', function (Blueprint $table) {
                $table->dropColumn('role_approval_status');
            });
        }
    }
};
