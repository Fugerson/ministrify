<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table) {
            // Drop the old unique on user_id alone (one Person per user globally)
            $table->dropUnique('people_user_id_unique');

            // Add composite unique: one Person per user per church
            $table->unique(['user_id', 'church_id'], 'people_user_id_church_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropUnique('people_user_id_church_id_unique');
            $table->unique('user_id', 'people_user_id_unique');
        });
    }
};
