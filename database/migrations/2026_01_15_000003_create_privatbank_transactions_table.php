<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('privatbank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->string('privat_id')->unique(); // PrivatBank transaction ID (tranId)
            $table->bigInteger('amount'); // In kopiykas (cents)
            $table->bigInteger('card_amount')->nullable(); // Amount on card
            $table->bigInteger('rest')->nullable(); // Balance after transaction
            $table->string('currency')->default('UAH');
            $table->timestamp('privat_time'); // Transaction time from PrivatBank
            $table->string('description')->nullable();
            $table->string('terminal')->nullable(); // Terminal info
            $table->string('counterpart_name')->nullable();
            $table->string('counterpart_okpo')->nullable(); // EDRPOU code
            $table->string('counterpart_mfo')->nullable(); // Bank MFO
            $table->string('counterpart_account')->nullable();

            // Link to our transaction system
            $table->foreignId('transaction_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('person_id')->nullable()->constrained()->onDelete('set null');

            $table->boolean('is_income')->default(true); // true = incoming, false = outgoing
            $table->boolean('is_processed')->default(false); // Has been linked/processed
            $table->boolean('is_ignored')->default(false); // User chose to ignore

            $table->timestamps();

            $table->index(['church_id', 'privat_time']);
            $table->index(['church_id', 'is_processed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('privatbank_transactions');
    }
};
