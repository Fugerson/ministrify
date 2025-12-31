<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sermon Series
        Schema::create('sermon_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->boolean('is_public')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['church_id', 'is_public']);
            $table->unique(['church_id', 'slug']);
        });

        // Sermons
        Schema::create('sermons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->foreignId('speaker_id')->nullable()->constrained('staff_members')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->string('title');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->date('sermon_date');
            $table->string('thumbnail')->nullable();

            $table->string('scripture_reference')->nullable();

            $table->enum('media_type', ['video', 'audio', 'both'])->default('video');
            $table->string('youtube_url')->nullable();
            $table->string('vimeo_url')->nullable();
            $table->string('audio_file')->nullable();
            $table->string('podcast_url')->nullable();

            $table->string('notes_pdf')->nullable();
            $table->string('slides_pdf')->nullable();

            $table->foreignId('sermon_series_id')->nullable()->constrained()->nullOnDelete();

            $table->integer('view_count')->default(0);
            $table->integer('duration_seconds')->nullable();

            $table->boolean('is_public')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->json('tags')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['church_id', 'is_public', 'sermon_date']);
            $table->index(['church_id', 'sermon_series_id']);
            $table->unique(['church_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sermons');
        Schema::dropIfExists('sermon_series');
    }
};
