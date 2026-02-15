<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            $table->foreignId('ministry_id')->nullable()->after('church_id')
                ->constrained('ministries')->nullOnDelete();

            $table->unique(['church_id', 'ministry_id']);
        });
    }

    public function down(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            $table->dropUnique(['church_id', 'ministry_id']);

            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['ministry_id']);
            }

            $table->dropColumn('ministry_id');
        });
    }
};
