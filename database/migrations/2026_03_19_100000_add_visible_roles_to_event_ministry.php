<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_ministry', function (Blueprint $table) {
            $table->json('visible_roles')->nullable()->after('ministry_id');
        });
    }

    public function down(): void
    {
        Schema::table('event_ministry', function (Blueprint $table) {
            $table->dropColumn('visible_roles');
        });
    }
};
