<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('board_epics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 7)->default('#6366f1');
            $table->text('description')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        Schema::table('board_cards', function (Blueprint $table) {
            $table->foreignId('epic_id')->nullable()->after('column_id')->constrained('board_epics')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('board_cards', function (Blueprint $table) {
            $table->dropConstrainedForeignId('epic_id');
        });

        Schema::dropIfExists('board_epics');
    }
};
