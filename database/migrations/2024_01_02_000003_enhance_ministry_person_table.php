<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ministry_person', function (Blueprint $table) {
            $table->string('role')->default('member')->after('person_id'); // leader, co-leader, member
            $table->string('position')->nullable()->after('role'); // Вокал, Гітара, Звук, etc.
            $table->string('experience_level')->default('beginner')->after('position'); // beginner, intermediate, advanced
            $table->date('joined_at')->nullable()->after('experience_level');
            $table->text('notes')->nullable()->after('joined_at');
        });
    }

    public function down(): void
    {
        Schema::table('ministry_person', function (Blueprint $table) {
            $table->dropColumn(['role', 'position', 'experience_level', 'joined_at', 'notes']);
        });
    }
};
