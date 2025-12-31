<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->foreignId('related_person_id')->constrained('people')->onDelete('cascade');
            $table->string('relationship_type'); // spouse, child, parent, sibling
            $table->timestamps();

            // Prevent duplicate relationships
            $table->unique(['person_id', 'related_person_id', 'relationship_type'], 'family_rel_unique');

            // Index for faster lookups
            $table->index(['person_id', 'relationship_type']);
            $table->index(['related_person_id', 'relationship_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_relationships');
    }
};
