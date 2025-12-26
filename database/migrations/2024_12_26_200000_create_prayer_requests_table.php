<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prayer_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->foreignId('person_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_public')->default(true);
            $table->boolean('is_urgent')->default(false);
            $table->enum('status', ['active', 'answered', 'closed'])->default('active');
            $table->text('answer_testimony')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->integer('prayer_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['church_id', 'status']);
            $table->index(['church_id', 'is_public', 'status']);
        });

        // Track who prayed for each request
        Schema::create('prayer_request_prayers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prayer_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('prayed_at');

            $table->unique(['prayer_request_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prayer_request_prayers');
        Schema::dropIfExists('prayer_requests');
    }
};
