<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->foreignId('subscription_plan_id')->nullable()->after('id')->constrained('subscription_plans')->nullOnDelete();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->enum('billing_period', ['monthly', 'yearly'])->default('monthly');
            $table->string('liqpay_customer_id')->nullable(); // для рекурентних платежів
        });
    }

    public function down(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->dropForeign(['subscription_plan_id']);
            $table->dropColumn(['subscription_plan_id', 'subscription_ends_at', 'billing_period', 'liqpay_customer_id']);
        });
    }
};
