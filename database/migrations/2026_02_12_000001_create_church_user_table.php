<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create church_user pivot table
        Schema::create('church_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->foreignId('church_role_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('person_id')->nullable()->constrained('people')->onDelete('set null');
            $table->json('permission_overrides')->nullable();
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'church_id']);
            $table->index('church_id');
        });

        // 2. Populate from existing users + people data
        DB::statement("
            INSERT INTO church_user (user_id, church_id, church_role_id, person_id, permission_overrides, joined_at, created_at, updated_at)
            SELECT
                u.id,
                u.church_id,
                u.church_role_id,
                p.id,
                u.permission_overrides,
                u.created_at,
                NOW(),
                NOW()
            FROM users u
            LEFT JOIN people p ON p.user_id = u.id AND p.church_id = u.church_id
            WHERE u.church_id IS NOT NULL
              AND u.deleted_at IS NULL
        ");

        // 3. Change FK on users.church_id: cascade → set null
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['church_id']);
            $table->foreign('church_id')
                ->references('id')
                ->on('churches')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        // Restore original FK
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['church_id']);
            $table->foreign('church_id')
                ->references('id')
                ->on('churches')
                ->onDelete('cascade');
        });

        Schema::dropIfExists('church_user');
    }
};
