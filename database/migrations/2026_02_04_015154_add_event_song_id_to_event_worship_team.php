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
        // Add event_song_id column (nullable for song-level assignments)
        Schema::table('event_worship_team', function (Blueprint $table) {
            $table->unsignedBigInteger('event_song_id')->nullable()->after('event_id');
            $table->foreign('event_song_id')->references('id')->on('event_songs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_worship_team', function (Blueprint $table) {
            $table->dropForeign(['event_song_id']);
            $table->dropColumn('event_song_id');
        });
    }
};
