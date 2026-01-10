<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('church_role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_role_id')->constrained()->cascadeOnDelete();
            $table->string('module');
            $table->json('actions')->nullable();
            $table->timestamps();

            $table->unique(['church_role_id', 'module']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('church_role_permissions');
    }
};
