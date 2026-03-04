<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_cell_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('role_type', 20); // 'ministry_role' or 'position'
            $table->unsignedBigInteger('role_id');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'role_type', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_cell_notes');
    }
};
