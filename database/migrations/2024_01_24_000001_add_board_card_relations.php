<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Related cards (many-to-many self-referential)
        Schema::create('board_card_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained('board_cards')->onDelete('cascade');
            $table->foreignId('related_card_id')->constrained('board_cards')->onDelete('cascade');
            $table->string('relation_type')->default('related'); // related, blocks, blocked_by, duplicate
            $table->timestamps();

            $table->unique(['card_id', 'related_card_id']);
        });

        // Add mentions to comments
        Schema::table('board_card_comments', function (Blueprint $table) {
            $table->json('mentions')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('board_card_relations');

        Schema::table('board_card_comments', function (Blueprint $table) {
            $table->dropColumn('mentions');
        });
    }
};
