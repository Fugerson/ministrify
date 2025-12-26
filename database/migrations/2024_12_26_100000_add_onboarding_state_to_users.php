<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'onboarding_state')) {
                $table->json('onboarding_state')->nullable()->after('onboarding_completed');
            }
            if (!Schema::hasColumn('users', 'onboarding_started_at')) {
                $table->timestamp('onboarding_started_at')->nullable()->after('onboarding_state');
            }
            if (!Schema::hasColumn('users', 'onboarding_completed_at')) {
                $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_started_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['onboarding_state', 'onboarding_started_at', 'onboarding_completed_at']);
        });
    }
};
