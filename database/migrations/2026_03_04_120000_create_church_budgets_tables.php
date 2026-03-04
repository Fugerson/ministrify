<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('church_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->year('year');
            $table->enum('status', ['draft', 'active', 'closed'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['church_id', 'year']);
        });

        Schema::create('church_budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->foreignId('church_budget_id')->constrained('church_budgets')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('transaction_categories')->nullOnDelete();
            $table->string('name');
            $table->boolean('is_recurring')->default(false);
            $table->json('amounts');
            $table->text('notes')->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['church_budget_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('church_budget_items');
        Schema::dropIfExists('church_budgets');
    }
};
