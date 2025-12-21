<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->foreignId('leader_id')->nullable()->constrained('people')->onDelete('set null');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('meeting_day')->nullable(); // monday, tuesday, etc.
            $table->time('meeting_time')->nullable();
            $table->string('location')->nullable();
            $table->string('color', 7)->default('#3b82f6');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('church_id');
        });

        Schema::create('group_person', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->foreignId('person_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('member'); // leader, co-leader, member
            $table->date('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['group_id', 'person_id']);
        });

        Schema::create('group_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->integer('total_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['group_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_attendances');
        Schema::dropIfExists('group_person');
        Schema::dropIfExists('groups');
    }
};
