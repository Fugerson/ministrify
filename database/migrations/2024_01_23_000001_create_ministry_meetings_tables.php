<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ministry Types (Media, Youth, Worship, etc.)
        Schema::create('ministry_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('icon')->nullable(); // emoji or icon class
            $table->string('color')->nullable(); // hex color
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Add type to ministries
        Schema::table('ministries', function (Blueprint $table) {
            $table->foreignId('type_id')->nullable()->after('church_id')->constrained('ministry_types')->nullOnDelete();
        });

        // Ministry Meetings (recurring meetings for each ministry)
        Schema::create('ministry_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ministry_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('location')->nullable();
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $table->string('theme')->nullable(); // Main theme/topic of meeting
            $table->text('notes')->nullable(); // General notes
            $table->text('summary')->nullable(); // Post-meeting summary
            $table->foreignId('copied_from_id')->nullable()->constrained('ministry_meetings')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['ministry_id', 'date']);
        });

        // Meeting Agenda Items (planning items for each meeting)
        Schema::create('meeting_agenda_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('ministry_meetings')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration_minutes')->nullable(); // estimated duration
            $table->foreignId('responsible_id')->nullable()->constrained('people')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });

        // Meeting Materials (files, links, resources)
        Schema::create('meeting_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('ministry_meetings')->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['link', 'file', 'note', 'video', 'audio', 'document']);
            $table->text('content'); // URL, file path, or note text
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Meeting Attendees (who attended)
        Schema::create('meeting_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('ministry_meetings')->cascadeOnDelete();
            $table->foreignId('person_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['invited', 'confirmed', 'attended', 'absent'])->default('invited');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['meeting_id', 'person_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_attendees');
        Schema::dropIfExists('meeting_materials');
        Schema::dropIfExists('meeting_agenda_items');
        Schema::dropIfExists('ministry_meetings');

        Schema::table('ministries', function (Blueprint $table) {
            if (DB::connection()->getDriverName() !== 'sqlite') {
                $table->dropForeign(['type_id']);
            }
            $table->dropColumn('type_id');
        });

        Schema::dropIfExists('ministry_types');
    }
};
