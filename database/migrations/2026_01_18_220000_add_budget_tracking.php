<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add expense_type to transactions
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('expense_type', ['recurring', 'one_time'])->nullable()->after('source_type');
        });

        // Create ministry_budgets table
        Schema::create('ministry_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->foreignId('ministry_id')->constrained()->onDelete('cascade');
            $table->decimal('monthly_budget', 12, 2)->default(0);
            $table->year('year');
            $table->unsignedTinyInteger('month');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['ministry_id', 'year', 'month']);
            $table->index(['church_id', 'year', 'month']);
        });

        // Add receipt_required flag to transaction_categories
        Schema::table('transaction_categories', function (Blueprint $table) {
            $table->boolean('receipt_required')->default(false)->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('expense_type');
        });

        Schema::dropIfExists('ministry_budgets');

        Schema::table('transaction_categories', function (Blueprint $table) {
            $table->dropColumn('receipt_required');
        });
    }
};
