<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Clean up garbage test data in church_id=16 (Благодать):
     * - Ministry "11" (ID=68) with no members
     * - Song "11" (ID=209) with 0 uses
     * - Transaction with amount=11 on 2026-03-11
     */
    public function up(): void
    {
        // Delete ministry "11" (ID=68, church_id=16)
        DB::table('ministries')
            ->where('id', 68)
            ->where('church_id', 16)
            ->where('name', '11')
            ->delete();

        // Delete song "11" (ID=209, church_id=16)
        DB::table('songs')
            ->where('id', 209)
            ->where('church_id', 16)
            ->where('title', '11')
            ->delete();

        // Delete transaction with amount 11 on 2026-03-11 (church_id=16)
        DB::table('transactions')
            ->where('church_id', 16)
            ->where('amount', 11)
            ->whereDate('date', '2026-03-11')
            ->delete();
    }

    public function down(): void
    {
        // No rollback needed for garbage data cleanup
    }
};
