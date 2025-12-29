<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop foreign key first
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['church_id']);
        });

        // Make nullable
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('church_id')->nullable()->change();
        });

        // Re-add foreign key with nullable
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('church_id')->references('id')->on('churches')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['church_id']);
            $table->unsignedBigInteger('church_id')->nullable(false)->change();
            $table->foreign('church_id')->references('id')->on('churches')->onDelete('cascade');
        });
    }
};
