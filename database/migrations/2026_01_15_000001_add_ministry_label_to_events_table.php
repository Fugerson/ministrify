<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('ministry_label')->nullable()->after('ministry_id');
        });

        // Make ministry_id nullable if it isn't already
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('ministry_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('ministry_label');
        });
    }
};
