<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ministry_roles', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Also add original_filename and mime_type to gallery_photos if missing
        if (! Schema::hasColumn('gallery_photos', 'original_filename')) {
            Schema::table('gallery_photos', function (Blueprint $table) {
                $table->string('original_filename')->nullable()->after('file_path');
                $table->string('mime_type')->nullable()->after('original_filename');
            });
        }
    }

    public function down(): void
    {
        Schema::table('ministry_roles', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('gallery_photos', function (Blueprint $table) {
            $table->dropColumn(['original_filename', 'mime_type']);
        });
    }
};
