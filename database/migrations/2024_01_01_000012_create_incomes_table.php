<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('income_categories')->onDelete('set null');
            $table->foreignId('person_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->string('description')->nullable();
            $table->enum('payment_method', ['cash', 'card', 'transfer', 'online'])->default('cash');
            $table->boolean('is_anonymous')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['church_id', 'date']);
            $table->index(['church_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
