<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_plan_items', function (Blueprint $table) {
            $table->foreignId('song_id')->nullable()->after('responsible_id')->constrained('songs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('service_plan_items', function (Blueprint $table) {
            $table->dropForeign(['song_id']);
            $table->dropColumn('song_id');
        });
    }
};
