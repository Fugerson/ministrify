<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // free, pro
            $table->string('name'); // Безкоштовний, Pro
            $table->text('description')->nullable();
            $table->integer('price_monthly')->default(0); // ціна в копійках
            $table->integer('price_yearly')->default(0);

            // Ліміти
            $table->integer('max_people')->default(0); // 0 = unlimited
            $table->integer('max_ministries')->default(0);
            $table->integer('max_events_per_month')->default(0);
            $table->integer('max_users')->default(0);

            // Фічі
            $table->boolean('has_telegram_bot')->default(false);
            $table->boolean('has_finances')->default(false);
            $table->boolean('has_forms')->default(false);
            $table->boolean('has_website_builder')->default(false);
            $table->boolean('has_custom_domain')->default(false);
            $table->boolean('has_api_access')->default(false);

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
