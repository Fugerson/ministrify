<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            // Add status column: active, paused, vacation
            $table->string('status', 20)->default('active')->after('is_active');
        });

        // Migrate existing data: if is_active = false, set status to 'paused'
        \DB::table('groups')->where('is_active', false)->update(['status' => 'paused']);
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
