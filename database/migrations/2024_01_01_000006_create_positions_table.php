<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ministry_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('ministry_person', function (Blueprint $table) {
            $table->foreignId('ministry_id')->constrained()->onDelete('cascade');
            $table->foreignId('person_id')->constrained()->onDelete('cascade');
            $table->json('position_ids')->nullable();
            $table->timestamps();
            $table->primary(['ministry_id', 'person_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ministry_person');
        Schema::dropIfExists('positions');
    }
};
