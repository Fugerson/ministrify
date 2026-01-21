<?php

use App\Models\Attendance;
use App\Models\Group;
use App\Models\GroupAttendance;
use App\Models\GroupAttendanceRecord;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migrate data from legacy GroupAttendance to new polymorphic Attendance system.
     */
    public function up(): void
    {
        // Check if source tables exist
        if (!Schema::hasTable('group_attendances')) {
            return;
        }

        // Get all legacy group attendance records that haven't been migrated
        $legacyAttendances = DB::table('group_attendances')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('attendances')
                    ->whereColumn('attendances.attendable_id', 'group_attendances.group_id')
                    ->where('attendances.attendable_type', Group::class)
                    ->whereColumn('attendances.date', 'group_attendances.date');
            })
            ->get();

        foreach ($legacyAttendances as $legacy) {
            // Create new Attendance record
            $attendance = Attendance::create([
                'church_id' => $legacy->church_id,
                'attendable_type' => Group::class,
                'attendable_id' => $legacy->group_id,
                'type' => Attendance::TYPE_GROUP,
                'date' => $legacy->date,
                'time' => $legacy->time,
                'location' => $legacy->location,
                'total_count' => $legacy->total_count,
                'members_present' => $legacy->members_present,
                'guests_count' => $legacy->guests_count,
                'recorded_by' => $legacy->recorded_by,
                'notes' => $legacy->notes,
                'created_at' => $legacy->created_at,
                'updated_at' => $legacy->updated_at,
            ]);

            // Migrate individual attendance records
            if (Schema::hasTable('group_attendance_records')) {
                $legacyRecords = DB::table('group_attendance_records')
                    ->where('group_attendance_id', $legacy->id)
                    ->get();

                foreach ($legacyRecords as $record) {
                    DB::table('attendance_records')->insert([
                        'attendance_id' => $attendance->id,
                        'person_id' => $record->person_id,
                        'present' => $record->present,
                        'checked_in_at' => $record->checked_in_at ?? null,
                        'notes' => $record->notes ?? null,
                        'created_at' => $record->created_at,
                        'updated_at' => $record->updated_at,
                    ]);
                }
            }
        }

        // Log migration summary
        $migratedCount = $legacyAttendances->count();
        if ($migratedCount > 0) {
            logger()->info("Migrated {$migratedCount} group attendance records to new Attendance system");
        }
    }

    /**
     * This migration is data-only and should not be reversed.
     * The legacy tables remain intact for safety.
     */
    public function down(): void
    {
        // We don't delete the migrated data to be safe
        // Original legacy data remains in group_attendances table
        logger()->info('GroupAttendance migration rollback: No action taken. Legacy data preserved.');
    }
};
