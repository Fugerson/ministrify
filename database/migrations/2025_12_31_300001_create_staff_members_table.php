<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->cascadeOnDelete();
            $table->foreignId('person_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('title');
            $table->string('role_category')->default('staff'); // pastor, staff, elder, deacon, volunteer
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();

            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('linkedin_url')->nullable();

            $table->boolean('is_public')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['church_id', 'is_public', 'sort_order']);
            $table->index(['church_id', 'role_category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_members');
    }
};
