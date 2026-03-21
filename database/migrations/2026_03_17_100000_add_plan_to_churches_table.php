<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->string('plan', 20)->default('free')->after('slug');
            $table->timestamp('plan_expires_at')->nullable()->after('plan');
            $table->timestamp('plan_changed_at')->nullable()->after('plan_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->dropColumn(['plan', 'plan_expires_at', 'plan_changed_at']);
        });
    }
};
