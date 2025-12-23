<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('board_card_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained('board_cards')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action'); // created, updated, moved, completed, comment_added, comment_edited, checklist_added, etc.
            $table->string('field')->nullable(); // which field was changed
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->json('metadata')->nullable(); // additional data
            $table->timestamps();

            $table->index(['card_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('board_card_activities');
    }
};
