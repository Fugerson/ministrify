<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->longText('content')->nullable()->after('description');
        });

        DB::statement("ALTER TABLE resources MODIFY COLUMN type ENUM('folder', 'file', 'document')");
    }

    public function down(): void
    {
        // Move documents back to files before removing enum value
        DB::table('resources')->where('type', 'document')->update(['type' => 'file']);

        DB::statement("ALTER TABLE resources MODIFY COLUMN type ENUM('folder', 'file')");

        Schema::table('resources', function (Blueprint $table) {
            $table->dropColumn('content');
        });
    }
};
