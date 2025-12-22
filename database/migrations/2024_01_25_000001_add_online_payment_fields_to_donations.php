<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            if (!Schema::hasColumn('donations', 'order_id')) {
                $table->string('order_id')->nullable()->after('transaction_id');
            }
            if (!Schema::hasColumn('donations', 'payment_id')) {
                $table->string('payment_id')->nullable()->after('order_id');
            }
            if (!Schema::hasColumn('donations', 'payment_data')) {
                $table->json('payment_data')->nullable()->after('payment_id');
            }
            if (!Schema::hasColumn('donations', 'donor_phone')) {
                $table->string('donor_phone')->nullable()->after('donor_email');
            }
            if (!Schema::hasColumn('donations', 'message')) {
                $table->text('message')->nullable()->after('purpose');
            }
            if (!Schema::hasColumn('donations', 'campaign_id')) {
                $table->foreignId('campaign_id')->nullable()->after('ministry_id')->constrained('donation_campaigns')->nullOnDelete();
            }
            if (!Schema::hasColumn('donations', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('status');
            }
        });

        // Add payment settings to churches
        Schema::table('churches', function (Blueprint $table) {
            if (!Schema::hasColumn('churches', 'payment_settings')) {
                $table->json('payment_settings')->nullable();
            }
        });

        // Update donation campaigns
        Schema::table('donation_campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('donation_campaigns', 'collected_amount')) {
                $table->decimal('collected_amount', 12, 2)->default(0)->after('goal_amount');
            }
            if (!Schema::hasColumn('donation_campaigns', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn(['order_id', 'payment_id', 'payment_data', 'donor_phone', 'message', 'paid_at']);
            $table->dropForeign(['campaign_id']);
            $table->dropColumn('campaign_id');
        });

        Schema::table('churches', function (Blueprint $table) {
            $table->dropColumn('payment_settings');
        });

        Schema::table('donation_campaigns', function (Blueprint $table) {
            $table->dropColumn(['collected_amount', 'slug']);
        });
    }
};
