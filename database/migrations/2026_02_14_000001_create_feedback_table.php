<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->enum('category', ['general', 'sermon', 'worship', 'suggestion', 'complaint'])->default('general');
            $table->text('message');
            $table->tinyInteger('rating')->nullable()->unsigned();
            $table->boolean('is_anonymous')->default(true);
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->enum('status', ['new', 'read', 'archived'])->default('new');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['church_id', 'status']);
            $table->index(['church_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
