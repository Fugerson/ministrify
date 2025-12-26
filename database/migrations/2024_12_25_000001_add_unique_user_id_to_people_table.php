<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds a unique constraint on user_id in people table.
     * A user should only be linked to one person profile.
     */
    public function up(): void
    {
        // First, clean up any duplicate user_id values (keep the oldest record)
        $duplicates = DB::table('people')
            ->select('user_id', DB::raw('MIN(id) as keep_id'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            DB::table('people')
                ->where('user_id', $dup->user_id)
                ->where('id', '!=', $dup->keep_id)
                ->update(['user_id' => null]);
        }

        // Add unique constraint
        Schema::table('people', function (Blueprint $table) {
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });
    }
};
