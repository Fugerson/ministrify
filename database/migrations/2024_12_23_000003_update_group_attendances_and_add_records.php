<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add new columns to group_attendances if they don't exist
        Schema::table('group_attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('group_attendances', 'church_id')) {
                $table->foreignId('church_id')->nullable()->after('group_id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('group_attendances', 'time')) {
                $table->time('time')->nullable()->after('date');
            }
            if (!Schema::hasColumn('group_attendances', 'location')) {
                $table->string('location')->nullable()->after('time');
            }
            if (!Schema::hasColumn('group_attendances', 'members_present')) {
                $table->integer('members_present')->default(0)->after('total_count');
            }
            if (!Schema::hasColumn('group_attendances', 'guests_count')) {
                $table->integer('guests_count')->default(0)->after('members_present');
            }
            if (!Schema::hasColumn('group_attendances', 'recorded_by')) {
                $table->foreignId('recorded_by')->nullable()->after('guests_count')->constrained('users')->nullOnDelete();
            }
        });

        // Create group_attendance_records table if not exists
        if (!Schema::hasTable('group_attendance_records')) {
            Schema::create('group_attendance_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('group_attendance_id')->constrained()->onDelete('cascade');
                $table->foreignId('person_id')->constrained()->onDelete('cascade');
                $table->boolean('present')->default(true);
                $table->time('checked_in_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['group_attendance_id', 'person_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('group_attendance_records');

        Schema::table('group_attendances', function (Blueprint $table) {
            $table->dropForeign(['church_id']);
            $table->dropForeign(['recorded_by']);
            $table->dropColumn(['church_id', 'time', 'location', 'members_present', 'guests_count', 'recorded_by']);
        });
    }
};
