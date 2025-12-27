<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('church_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->string('color', 7)->default('#6b7280');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['church_id', 'slug']);
        });

        // Update people table to use foreign key
        Schema::table('people', function (Blueprint $table) {
            $table->foreignId('church_role_id')->nullable()->after('church_role')->constrained('church_roles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropForeign(['church_role_id']);
            $table->dropColumn('church_role_id');
        });

        Schema::dropIfExists('church_roles');
    }
};
