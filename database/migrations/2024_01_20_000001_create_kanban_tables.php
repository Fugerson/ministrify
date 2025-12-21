<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kanban Boards
        Schema::create('boards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color')->default('#6366f1');
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });

        // Board Columns (To Do, In Progress, Done, etc.)
        Schema::create('board_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('color')->default('#gray');
            $table->integer('position')->default(0);
            $table->integer('card_limit')->nullable();
            $table->timestamps();
        });

        // Board Cards (Tasks)
        Schema::create('board_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('column_id')->constrained('board_columns')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('position')->default(0);
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->date('due_date')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('people')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('labels')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // Card Comments
        Schema::create('board_card_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained('board_cards')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->timestamps();
        });

        // Card Attachments
        Schema::create('board_card_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained('board_cards')->onDelete('cascade');
            $table->string('name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->integer('size')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Card Checklist Items
        Schema::create('board_card_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained('board_cards')->onDelete('cascade');
            $table->string('title');
            $table->boolean('is_completed')->default(false);
            $table->integer('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('board_card_checklist_items');
        Schema::dropIfExists('board_card_attachments');
        Schema::dropIfExists('board_card_comments');
        Schema::dropIfExists('board_cards');
        Schema::dropIfExists('board_columns');
        Schema::dropIfExists('boards');
    }
};
