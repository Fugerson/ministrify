<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->enum('direction', ['incoming', 'outgoing']);
            $table->text('message');
            $table->bigInteger('telegram_message_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            
            $table->index(['church_id', 'person_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_messages');
    }
};
