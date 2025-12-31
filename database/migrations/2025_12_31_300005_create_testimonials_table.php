<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->foreignId('person_id')->nullable()->constrained()->nullOnDelete();

            $table->string('author_name');
            $table->string('author_photo')->nullable();
            $table->string('author_role')->nullable();

            $table->string('title')->nullable();
            $table->text('content');

            $table->string('video_url')->nullable();
            $table->string('category')->nullable();

            $table->boolean('is_public')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['church_id', 'is_public', 'is_featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
