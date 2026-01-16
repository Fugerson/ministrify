<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ministries', function (Blueprint $table) {
            // JSON array of person IDs who have access regardless of visibility
            $table->json('allowed_person_ids')->nullable()->after('visibility');
        });
    }

    public function down(): void
    {
        Schema::table('ministries', function (Blueprint $table) {
            $table->dropColumn('allowed_person_ids');
        });
    }
};
