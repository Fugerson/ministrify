<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ministries', function (Blueprint $table) {
            // visibility: 'public' = everyone, 'members' = only members, 'leaders' = only admins/leaders
            $table->string('visibility')->default('public')->after('is_private');
        });

        // Migrate existing is_private values to visibility
        DB::table('ministries')->where('is_private', true)->update(['visibility' => 'members']);
    }

    public function down(): void
    {
        Schema::table('ministries', function (Blueprint $table) {
            $table->dropColumn('visibility');
        });
    }
};
