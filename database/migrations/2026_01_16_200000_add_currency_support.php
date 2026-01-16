<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create exchange_rates table
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code', 3); // USD, EUR
            $table->decimal('rate', 12, 6); // rate to UAH
            $table->date('date');
            $table->string('source', 20)->default('nbu');
            $table->timestamps();

            $table->unique(['currency_code', 'date']);
            $table->index('date');
        });

        // Add amount_uah to transactions for quick totals
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('amount_uah', 12, 2)->nullable()->after('currency');
        });

        // Add enabled_currencies to churches
        Schema::table('churches', function (Blueprint $table) {
            $table->json('enabled_currencies')->nullable();
        });

        // Set amount_uah for existing UAH transactions
        \DB::table('transactions')
            ->where('currency', 'UAH')
            ->update(['amount_uah' => \DB::raw('amount')]);
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('amount_uah');
        });

        Schema::table('churches', function (Blueprint $table) {
            $table->dropColumn('enabled_currencies');
        });

        Schema::dropIfExists('exchange_rates');
    }
};
