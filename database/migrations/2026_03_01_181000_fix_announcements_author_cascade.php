<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign(['author_id']);
            $table->foreignId('author_id')->nullable()->change();
            $table->foreign('author_id')->references('id')->on('users')->nullOnDelete();
        });

        // Also fix blog_posts author cascade
        if (Schema::hasTable('blog_posts')) {
            Schema::table('blog_posts', function (Blueprint $table) {
                $table->dropForeign(['author_id']);
                $table->foreignId('author_id')->nullable()->change();
                $table->foreign('author_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign(['author_id']);
            $table->foreign('author_id')->references('id')->on('users')->cascadeOnDelete();
        });

        if (Schema::hasTable('blog_posts')) {
            Schema::table('blog_posts', function (Blueprint $table) {
                $table->dropForeign(['author_id']);
                $table->foreign('author_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }
};
