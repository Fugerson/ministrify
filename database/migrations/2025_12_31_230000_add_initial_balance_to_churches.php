<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->decimal('initial_balance', 12, 2)->default(0)->after('settings');
            $table->date('initial_balance_date')->nullable()->after('initial_balance');
        });
    }

    public function down(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->dropColumn(['initial_balance', 'initial_balance_date']);
        });
    }
};
