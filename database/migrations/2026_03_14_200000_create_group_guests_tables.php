<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['group_id', 'deleted_at']);
        });

        Schema::create('group_guest_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_guest_id')->constrained('group_guests')->onDelete('cascade');
            $table->foreignId('attendance_id')->constrained('attendances')->onDelete('cascade');
            $table->boolean('present')->default(false);
            $table->timestamps();

            $table->unique(['group_guest_id', 'attendance_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_guest_attendance');
        Schema::dropIfExists('group_guests');
    }
};
