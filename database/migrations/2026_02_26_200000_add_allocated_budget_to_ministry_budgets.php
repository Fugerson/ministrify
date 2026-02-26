<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ministry_budgets', function (Blueprint $table) {
            $table->decimal('allocated_budget', 12, 2)->default(0)->after('monthly_budget');
        });
    }

    public function down(): void
    {
        Schema::table('ministry_budgets', function (Blueprint $table) {
            $table->dropColumn('allocated_budget');
        });
    }
};
