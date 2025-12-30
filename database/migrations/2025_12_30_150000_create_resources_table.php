<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('resources')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['folder', 'file']);
            $table->string('file_path')->nullable();
            $table->bigInteger('file_size')->nullable(); // in bytes
            $table->string('mime_type')->nullable();
            $table->string('icon')->nullable(); // emoji or icon class
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['church_id', 'parent_id']);
            $table->index(['church_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
