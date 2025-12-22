<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('board_cards', function (Blueprint $table) {
            // Link cards to other entities for integration
            $table->foreignId('event_id')->nullable()->after('labels')->constrained()->onDelete('set null');
            $table->foreignId('ministry_id')->nullable()->after('event_id')->constrained()->onDelete('set null');
            $table->foreignId('group_id')->nullable()->after('ministry_id')->constrained()->onDelete('set null');
            $table->foreignId('person_id')->nullable()->after('group_id')->constrained()->onDelete('set null');

            // Quick reference type for filtering
            $table->string('entity_type')->nullable()->after('person_id'); // event, ministry, group, person
        });
    }

    public function down(): void
    {
        Schema::table('board_cards', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropForeign(['ministry_id']);
            $table->dropForeign(['group_id']);
            $table->dropForeign(['person_id']);
            $table->dropColumn(['event_id', 'ministry_id', 'group_id', 'person_id', 'entity_type']);
        });
    }
};
