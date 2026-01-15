<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add IBAN to people for auto-matching
        Schema::table('people', function (Blueprint $table) {
            $table->string('iban')->nullable()->after('email');
        });

        // Create sender-category mapping for smart categorization
        Schema::create('monobank_sender_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->string('sender_iban')->nullable();
            $table->string('sender_name')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('transaction_categories')->onDelete('cascade');
            $table->foreignId('person_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('times_used')->default(1);
            $table->timestamps();

            $table->index(['church_id', 'sender_iban']);
            $table->index(['church_id', 'sender_name']);
        });

        // Add webhook URL to churches for real-time sync
        Schema::table('churches', function (Blueprint $table) {
            $table->string('monobank_webhook_secret')->nullable()->after('monobank_last_sync');
        });
    }

    public function down(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->dropColumn('monobank_webhook_secret');
        });

        Schema::dropIfExists('monobank_sender_mappings');

        Schema::table('people', function (Blueprint $table) {
            $table->dropColumn('iban');
        });
    }
};
