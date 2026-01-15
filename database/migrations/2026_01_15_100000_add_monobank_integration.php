<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add monobank settings to churches
        Schema::table('churches', function (Blueprint $table) {
            $table->text('monobank_token')->nullable();
            $table->string('monobank_account_id')->nullable();
            $table->boolean('monobank_auto_sync')->default(false);
            $table->timestamp('monobank_last_sync')->nullable();
        });

        // Create table for imported monobank transactions
        Schema::create('monobank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->string('mono_id')->unique(); // Monobank transaction ID
            $table->bigInteger('amount'); // In kopiykas (cents)
            $table->bigInteger('balance')->nullable(); // Balance after transaction
            $table->integer('cashback_amount')->default(0);
            $table->integer('commission_rate')->default(0);
            $table->string('currency_code')->default('980'); // 980 = UAH
            $table->timestamp('mono_time'); // Transaction time from Monobank
            $table->string('description')->nullable();
            $table->string('comment')->nullable(); // User comment in transfer
            $table->integer('mcc')->nullable(); // Merchant category code
            $table->string('counterpart_iban')->nullable();
            $table->string('counterpart_name')->nullable();

            // Link to our transaction system
            $table->foreignId('transaction_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('person_id')->nullable()->constrained()->onDelete('set null');

            $table->boolean('is_income')->default(true); // true = incoming, false = outgoing
            $table->boolean('is_processed')->default(false); // Has been linked/processed
            $table->boolean('is_ignored')->default(false); // User chose to ignore

            $table->timestamps();

            $table->index(['church_id', 'mono_time']);
            $table->index(['church_id', 'is_processed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monobank_transactions');

        Schema::table('churches', function (Blueprint $table) {
            $table->dropColumn([
                'monobank_token',
                'monobank_account_id',
                'monobank_auto_sync',
                'monobank_last_sync',
            ]);
        });
    }
};
