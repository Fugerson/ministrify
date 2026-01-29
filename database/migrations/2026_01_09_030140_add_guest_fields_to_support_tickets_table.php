<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            // Add guest fields for anonymous contact form submissions
            $table->string('guest_name')->nullable()->after('user_id');
            $table->string('guest_email')->nullable()->after('guest_name');
        });

        // Make user_id nullable for guest tickets (requires DBAL, skip on SQLite)
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('support_tickets', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->foreignId('user_id')->nullable()->change();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });

            Schema::table('support_messages', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->foreignId('user_id')->nullable()->change();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('support_messages', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->foreignId('user_id')->nullable(false)->change();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });

            Schema::table('support_tickets', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->foreignId('user_id')->nullable(false)->change();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn(['guest_name', 'guest_email']);
        });
    }
};
