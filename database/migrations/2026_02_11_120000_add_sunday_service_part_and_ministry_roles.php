<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ministries', function (Blueprint $table) {
            $table->boolean('is_sunday_service_part')->default(false)->after('is_worship_ministry');
        });

        Schema::create('ministry_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ministry_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('event_ministry_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('ministry_id')->constrained()->onDelete('cascade');
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->foreignId('ministry_role_id')->constrained()->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'ministry_id', 'person_id', 'ministry_role_id'], 'event_ministry_team_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_ministry_team');
        Schema::dropIfExists('ministry_roles');

        Schema::table('ministries', function (Blueprint $table) {
            $table->dropColumn('is_sunday_service_part');
        });
    }
};
