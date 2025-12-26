<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('online_donations')) {
            return;
        }

        Schema::create('online_donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->foreignId('person_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();

            $table->string('provider'); // liqpay, monobank
            $table->string('provider_order_id')->nullable();
            $table->string('provider_payment_id')->nullable();

            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('UAH');
            $table->string('status')->default('pending'); // pending, processing, success, failed, refunded

            $table->string('donor_name')->nullable();
            $table->string('donor_email')->nullable();
            $table->string('donor_phone')->nullable();
            $table->boolean('is_anonymous')->default(false);

            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_id')->nullable();

            $table->text('description')->nullable();
            $table->json('provider_response')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['church_id', 'status']);
            $table->index(['provider', 'provider_order_id']);
        });

        // Add payment settings to churches if not exists
        if (!Schema::hasColumn('churches', 'payment_settings')) {
            Schema::table('churches', function (Blueprint $table) {
                $table->json('payment_settings')->nullable()->after('settings');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('online_donations');

        Schema::table('churches', function (Blueprint $table) {
            $table->dropColumn('payment_settings');
        });
    }
};
