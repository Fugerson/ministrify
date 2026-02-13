<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add shepherds_enabled to churches
        Schema::table('churches', function (Blueprint $table) {
            $table->boolean('shepherds_enabled')->default(false)->after('public_site_enabled');
        });

        // Add shepherd fields to people
        Schema::table('people', function (Blueprint $table) {
            // Boolean to mark if this person can be a shepherd
            $table->boolean('is_shepherd')->default(false)->after('church_role_id');

            // Self-referential foreign key for assigned shepherd
            $table->foreignId('shepherd_id')
                ->nullable()
                ->after('is_shepherd')
                ->constrained('people')
                ->nullOnDelete();

            // Index for efficient queries
            $table->index(['church_id', 'is_shepherd']);
        });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            if (DB::connection()->getDriverName() !== 'sqlite') {
                $table->dropForeign(['shepherd_id']);
            }
            $table->dropIndex(['church_id', 'is_shepherd']);
            $table->dropColumn(['is_shepherd', 'shepherd_id']);
        });

        Schema::table('churches', function (Blueprint $table) {
            $table->dropColumn('shepherds_enabled');
        });
    }
};
