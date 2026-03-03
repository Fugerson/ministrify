<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ministries', function (Blueprint $table) {
            $table->json('settings')->nullable()->after('allowed_person_ids');
        });
    }

    public function down(): void
    {
        Schema::table('ministries', function (Blueprint $table) {
            $table->dropColumn('settings');
        });
    }
};
