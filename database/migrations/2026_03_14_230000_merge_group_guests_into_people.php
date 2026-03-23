<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Migrate each GroupGuest to Person + group_person pivot
        $guests = DB::table('group_guests')->whereNull('deleted_at')->get();

        foreach ($guests as $guest) {
            // Create Person record
            $personId = DB::table('people')->insertGetId([
                'church_id' => $guest->church_id,
                'first_name' => $guest->first_name,
                'last_name' => $guest->last_name,
                'photo' => $guest->photo,
                'birth_date' => $guest->birth_date,
                'notes' => $guest->notes,
                'membership_status' => 'guest',
                'created_at' => $guest->created_at,
                'updated_at' => $guest->updated_at,
            ]);

            // Add to group with role='guest'
            DB::table('group_person')->insert([
                'group_id' => $guest->group_id,
                'person_id' => $personId,
                'role' => 'guest',
                'joined_at' => $guest->created_at ? date('Y-m-d', strtotime($guest->created_at)) : null,
                'created_at' => $guest->created_at,
                'updated_at' => $guest->updated_at,
            ]);

            // Migrate attendance records
            $guestAttendances = DB::table('group_guest_attendance')
                ->where('group_guest_id', $guest->id)
                ->get();

            foreach ($guestAttendances as $ga) {
                // Check if record already exists (shouldn't, but safety)
                $exists = DB::table('attendance_records')
                    ->where('attendance_id', $ga->attendance_id)
                    ->where('person_id', $personId)
                    ->exists();

                if (! $exists) {
                    DB::table('attendance_records')->insert([
                        'attendance_id' => $ga->attendance_id,
                        'person_id' => $personId,
                        'present' => $ga->present,
                        'created_at' => $ga->created_at,
                        'updated_at' => $ga->updated_at,
                    ]);
                }
            }
        }

        // 2. Drop the old tables
        Schema::dropIfExists('group_guest_attendance');
        Schema::dropIfExists('group_guests');
    }

    public function down(): void
    {
        // Recreate tables (without data restoration)
        Schema::create('group_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('photo')->nullable();
            $table->date('birth_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['group_id', 'deleted_at']);
        });

        Schema::create('group_guest_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_guest_id')->constrained('group_guests')->onDelete('cascade');
            $table->foreignId('attendance_id')->constrained('attendances')->onDelete('cascade');
            $table->boolean('present')->default(false);
            $table->timestamps();
            $table->unique(['group_guest_id', 'attendance_id']);
        });
    }
};
