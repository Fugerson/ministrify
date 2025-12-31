<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Blog Categories
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('color')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['church_id', 'slug']);
        });

        // Blog Posts
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();

            $table->string('title');
            $table->string('slug')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();

            $table->foreignId('blog_category_id')->nullable()->constrained()->nullOnDelete();
            $table->json('tags')->nullable();

            $table->enum('status', ['draft', 'published', 'scheduled', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->integer('view_count')->default(0);

            $table->boolean('allow_comments')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_pinned')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['church_id', 'status', 'published_at']);
            $table->unique(['church_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_categories');
    }
};
