<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->nullable()->constrained()->nullOnDelete();

            $table->string('order_id')->unique(); // наш ID замовлення
            $table->string('liqpay_order_id')->nullable(); // ID від LiqPay
            $table->string('liqpay_payment_id')->nullable();

            $table->integer('amount'); // сума в копійках
            $table->string('currency', 3)->default('UAH');
            $table->string('description');

            $table->enum('status', ['pending', 'success', 'failure', 'reversed'])->default('pending');
            $table->enum('type', ['subscription', 'one_time'])->default('subscription');
            $table->enum('period', ['monthly', 'yearly'])->nullable();

            $table->json('liqpay_data')->nullable(); // повні дані від LiqPay
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['church_id', 'status']);
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
