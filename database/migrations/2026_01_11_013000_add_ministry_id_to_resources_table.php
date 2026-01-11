<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->foreignId('ministry_id')->nullable()->after('church_id')->constrained()->nullOnDelete();
            $table->index(['church_id', 'ministry_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->dropForeign(['ministry_id']);
            $table->dropIndex(['church_id', 'ministry_id']);
            $table->dropColumn('ministry_id');
        });
    }
};
