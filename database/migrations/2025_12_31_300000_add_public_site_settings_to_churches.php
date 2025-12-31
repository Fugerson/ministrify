<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->string('public_template')->default('modern')->after('public_site_enabled');
            $table->json('public_site_settings')->nullable()->after('public_template');
        });
    }

    public function down(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->dropColumn(['public_template', 'public_site_settings']);
        });
    }
};
