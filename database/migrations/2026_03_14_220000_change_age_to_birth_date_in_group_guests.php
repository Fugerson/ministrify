<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_guests', function (Blueprint $table) {
            $table->date('birth_date')->nullable()->after('photo');
        });

        // Convert existing age values to approximate birth_date
        DB::table('group_guests')->whereNotNull('age')->where('age', '>', 0)->get()->each(function ($guest) {
            DB::table('group_guests')->where('id', $guest->id)->update([
                'birth_date' => now()->subYears($guest->age)->format('Y-m-d'),
            ]);
        });

        Schema::table('group_guests', function (Blueprint $table) {
            $table->dropColumn('age');
        });
    }

    public function down(): void
    {
        Schema::table('group_guests', function (Blueprint $table) {
            $table->unsignedTinyInteger('age')->nullable()->after('photo');
        });

        DB::table('group_guests')->whereNotNull('birth_date')->get()->each(function ($guest) {
            DB::table('group_guests')->where('id', $guest->id)->update([
                'age' => now()->diffInYears($guest->birth_date),
            ]);
        });

        Schema::table('group_guests', function (Blueprint $table) {
            $table->dropColumn('birth_date');
        });
    }
};
