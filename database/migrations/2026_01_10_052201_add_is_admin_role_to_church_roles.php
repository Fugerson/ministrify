<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('church_roles', function (Blueprint $table) {
            $table->boolean('is_admin_role')->default(false)->after('is_default');
        });
    }

    public function down(): void
    {
        Schema::table('church_roles', function (Blueprint $table) {
            $table->dropColumn('is_admin_role');
        });
    }
};
