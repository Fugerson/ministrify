<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add has_music flag to events
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('has_music')->default(false)->after('is_service');
        });

        // Worship roles (instruments/positions)
        Schema::create('worship_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Person worship skills (what instruments/roles a person can play)
        Schema::create('person_worship_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained()->onDelete('cascade');
            $table->foreignId('worship_role_id')->constrained()->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['person_id', 'worship_role_id']);
        });

        // Event worship team (who plays what at an event)
        Schema::create('event_worship_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('person_id')->constrained()->onDelete('cascade');
            $table->foreignId('worship_role_id')->constrained()->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'person_id', 'worship_role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_worship_team');
        Schema::dropIfExists('person_worship_skills');
        Schema::dropIfExists('worship_roles');

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('has_music');
        });
    }
};
