<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('artist')->nullable();
            $table->string('key')->nullable(); // C, D, E, F, G, A, B + m for minor
            $table->integer('bpm')->nullable();
            $table->text('lyrics')->nullable();
            $table->text('chords')->nullable(); // ChordPro format
            $table->string('ccli_number')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('spotify_url')->nullable();
            $table->json('tags')->nullable(); // ["worship", "fast", "opening"]
            $table->integer('times_used')->default(0);
            $table->date('last_used_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['church_id', 'title']);
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->fullText(['title', 'artist', 'lyrics']);
            }
        });

        // Setlist - songs for a specific event
        Schema::create('event_songs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('song_id')->constrained()->cascadeOnDelete();
            $table->integer('order')->default(0);
            $table->string('key')->nullable(); // Override song default key
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'song_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_songs');
        Schema::dropIfExists('songs');
    }
};
