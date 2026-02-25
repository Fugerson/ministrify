<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ministry_budget_id')->constrained('ministry_budgets')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('transaction_categories')->nullOnDelete();
            $table->string('name');
            $table->decimal('planned_amount', 12, 2);
            $table->text('notes')->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['ministry_budget_id', 'sort_order']);
        });

        Schema::create('budget_item_person', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('person_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['budget_item_id', 'person_id']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('budget_item_id')->nullable()->after('ministry_id')->constrained('budget_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('budget_item_id');
        });

        Schema::dropIfExists('budget_item_person');
        Schema::dropIfExists('budget_items');
    }
};
