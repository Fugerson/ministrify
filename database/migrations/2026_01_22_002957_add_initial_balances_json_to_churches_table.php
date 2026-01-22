<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->json('initial_balances')->nullable()->after('initial_balance_date');
        });

        // Migrate existing initial_balance to initial_balances JSON
        DB::table('churches')->whereNotNull('initial_balance')->where('initial_balance', '>', 0)->get()->each(function ($church) {
            DB::table('churches')
                ->where('id', $church->id)
                ->update([
                    'initial_balances' => json_encode(['UAH' => (float) $church->initial_balance])
                ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Migrate back: extract UAH from initial_balances to initial_balance
        DB::table('churches')->whereNotNull('initial_balances')->get()->each(function ($church) {
            $balances = json_decode($church->initial_balances, true);
            if (isset($balances['UAH'])) {
                DB::table('churches')
                    ->where('id', $church->id)
                    ->update(['initial_balance' => $balances['UAH']]);
            }
        });

        Schema::table('churches', function (Blueprint $table) {
            $table->dropColumn('initial_balances');
        });
    }
};
