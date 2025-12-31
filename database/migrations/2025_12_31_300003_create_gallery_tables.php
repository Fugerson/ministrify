<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Galleries (Albums)
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->string('title');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->date('event_date')->nullable();

            $table->boolean('is_public')->default(true);
            $table->integer('sort_order')->default(0);
            $table->integer('photo_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['church_id', 'is_public', 'sort_order']);
            $table->unique(['church_id', 'slug']);
        });

        // Gallery Photos
        Schema::create('gallery_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained()->cascadeOnDelete();

            $table->string('file_path');
            $table->string('thumbnail_path')->nullable();
            $table->string('caption')->nullable();
            $table->string('alt_text')->nullable();

            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('file_size')->nullable();

            $table->integer('sort_order')->default(0);
            $table->boolean('is_cover')->default(false);

            $table->timestamps();

            $table->index(['gallery_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_photos');
        Schema::dropIfExists('galleries');
    }
};
