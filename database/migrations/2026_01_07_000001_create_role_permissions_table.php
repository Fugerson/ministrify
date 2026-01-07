<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->string('role'); // admin, leader, volunteer
            $table->string('module'); // people, groups, ministries, events, finances, etc.
            $table->json('permissions'); // ['view', 'create', 'edit', 'delete']
            $table->timestamps();

            $table->unique(['church_id', 'role', 'module']);
            $table->index(['church_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
