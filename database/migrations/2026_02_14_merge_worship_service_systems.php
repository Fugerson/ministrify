<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add event_song_id to event_ministry_team (skip if already exists from partial run)
        if (!Schema::hasColumn('event_ministry_team', 'event_song_id')) {
            Schema::table('event_ministry_team', function (Blueprint $table) {
                $table->unsignedBigInteger('event_song_id')->nullable()->after('ministry_role_id');
                $table->foreign('event_song_id')->references('id')->on('event_songs')->onDelete('cascade');
            });
        }

        // 2. Drop unique constraint from event_ministry_team (needed for song-level duplicates)
        // First create a regular index so MySQL FK can use it instead of the unique one
        Schema::table('event_ministry_team', function (Blueprint $table) {
            $table->index('event_id', 'event_ministry_team_event_id_index');
        });
        Schema::table('event_ministry_team', function (Blueprint $table) {
            $table->dropUnique('event_ministry_team_unique');
        });

        // 3. Copy WorshipRole → MinistryRole for each worship ministry
        $worshipMinistries = DB::table('ministries')
            ->where('is_worship_ministry', true)
            ->get();

        $churchWorshipRoles = DB::table('worship_roles')->get()->groupBy('church_id');

        foreach ($worshipMinistries as $ministry) {
            $worshipRoles = $churchWorshipRoles->get($ministry->church_id, collect());

            foreach ($worshipRoles as $wr) {
                DB::table('ministry_roles')->insert([
                    'ministry_id' => $ministry->id,
                    'name' => $wr->name,
                    'icon' => $wr->icon,
                    'color' => $wr->color,
                    'sort_order' => $wr->sort_order,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 4. Migrate event_worship_team → event_ministry_team
        // Build mapping: worship_role_id → ministry_role_id (per ministry)
        $worshipTeamRecords = DB::table('event_worship_team')->get();

        if ($worshipTeamRecords->isNotEmpty()) {
            // For each worship ministry, build a map of worship_role name → ministry_role id
            foreach ($worshipMinistries as $ministry) {
                $ministryRolesByName = DB::table('ministry_roles')
                    ->where('ministry_id', $ministry->id)
                    ->pluck('id', 'name');

                // Get events belonging to this ministry's church
                $churchEventIds = DB::table('events')
                    ->where('church_id', $ministry->church_id)
                    ->pluck('id');

                $relevantRecords = $worshipTeamRecords->whereIn('event_id', $churchEventIds);

                foreach ($relevantRecords as $record) {
                    // Get the worship role name to find corresponding ministry role
                    $worshipRoleName = DB::table('worship_roles')
                        ->where('id', $record->worship_role_id)
                        ->value('name');

                    if (!$worshipRoleName || !isset($ministryRolesByName[$worshipRoleName])) {
                        continue;
                    }

                    $ministryRoleId = $ministryRolesByName[$worshipRoleName];

                    // Check if already exists
                    $exists = DB::table('event_ministry_team')
                        ->where('event_id', $record->event_id)
                        ->where('ministry_id', $ministry->id)
                        ->where('person_id', $record->person_id)
                        ->where('ministry_role_id', $ministryRoleId)
                        ->where('event_song_id', $record->event_song_id)
                        ->exists();

                    if (!$exists) {
                        DB::table('event_ministry_team')->insert([
                            'event_id' => $record->event_id,
                            'ministry_id' => $ministry->id,
                            'person_id' => $record->person_id,
                            'ministry_role_id' => $ministryRoleId,
                            'event_song_id' => $record->event_song_id,
                            'notes' => $record->notes,
                            'created_at' => $record->created_at,
                            'updated_at' => $record->updated_at,
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        // Remove migrated records (those with event_song_id set)
        DB::table('event_ministry_team')->whereNotNull('event_song_id')->delete();

        // Remove ministry roles that were copied from worship roles
        $worshipMinistries = DB::table('ministries')
            ->where('is_worship_ministry', true)
            ->pluck('id');

        $worshipRoleNames = DB::table('worship_roles')->pluck('name')->unique();

        DB::table('ministry_roles')
            ->whereIn('ministry_id', $worshipMinistries)
            ->whereIn('name', $worshipRoleNames)
            ->delete();

        // Re-add unique constraint and drop helper index
        Schema::table('event_ministry_team', function (Blueprint $table) {
            $table->unique(['event_id', 'ministry_id', 'person_id', 'ministry_role_id'], 'event_ministry_team_unique');
            $table->dropIndex('event_ministry_team_event_id_index');
        });

        // Remove event_song_id column
        Schema::table('event_ministry_team', function (Blueprint $table) {
            $table->dropForeign(['event_song_id']);
            $table->dropColumn('event_song_id');
        });
    }
};
