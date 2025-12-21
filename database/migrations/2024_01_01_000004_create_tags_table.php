<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('color')->nullable();
            $table->timestamps();

            $table->unique(['church_id', 'name']);
        });

        Schema::create('person_tag', function (Blueprint $table) {
            $table->foreignId('person_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->primary(['person_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_tag');
        Schema::dropIfExists('tags');
    }
};
