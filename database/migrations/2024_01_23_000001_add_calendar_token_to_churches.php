<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->string('calendar_token', 64)->nullable()->unique()->after('settings');
        });

        // Generate tokens for existing churches
        \DB::table('churches')->whereNull('calendar_token')->get()->each(function ($church) {
            \DB::table('churches')->where('id', $church->id)->update(['calendar_token' => Str::random(32)]);
        });
    }

    public function down(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->dropColumn('calendar_token');
        });
    }
};
