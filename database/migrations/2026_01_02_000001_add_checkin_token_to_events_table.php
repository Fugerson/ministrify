<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('checkin_token', 32)->nullable()->unique()->after('cover_image');
            $table->boolean('qr_checkin_enabled')->default(false)->after('checkin_token');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['checkin_token', 'qr_checkin_enabled']);
        });
    }
};
