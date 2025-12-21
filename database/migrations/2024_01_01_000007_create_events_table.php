<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->foreignId('ministry_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->date('date');
            $table->time('time');
            $table->text('notes')->nullable();
            $table->string('recurrence_rule')->nullable();
            $table->foreignId('parent_event_id')->nullable()->constrained('events')->onDelete('cascade');
            $table->timestamps();

            $table->index(['church_id', 'date']);
            $table->index(['ministry_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
