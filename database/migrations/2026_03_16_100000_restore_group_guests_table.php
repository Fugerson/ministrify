<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create group_guests table
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
            $table->index('church_id');
        });

        // 2. Create group_guest_attendance table
        Schema::create('group_guest_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_guest_id')->constrained('group_guests')->onDelete('cascade');
            $table->foreignId('attendance_id')->constrained('attendances')->onDelete('cascade');
            $table->boolean('present')->default(false);
            $table->timestamps();

            $table->unique(['group_guest_id', 'attendance_id']);
        });

        // 3. Migrate guest Person records back to group_guests
        $guestPersons = DB::table('people')
            ->where('membership_status', 'guest')
            ->whereNull('deleted_at')
            ->get();

        foreach ($guestPersons as $person) {
            // Find which group(s) this guest belongs to
            $pivots = DB::table('group_person')
                ->where('person_id', $person->id)
                ->where('role', 'guest')
                ->get();

            foreach ($pivots as $pivot) {
                // Create group_guest record
                $guestId = DB::table('group_guests')->insertGetId([
                    'group_id' => $pivot->group_id,
                    'church_id' => $person->church_id,
                    'first_name' => $person->first_name,
                    'last_name' => $person->last_name,
                    'photo' => $person->photo,
                    'birth_date' => $person->birth_date,
                    'notes' => $person->notes,
                    'created_at' => $person->created_at,
                    'updated_at' => $person->updated_at,
                ]);

                // Migrate attendance records for this guest
                $attendanceRecords = DB::table('attendance_records')
                    ->where('person_id', $person->id)
                    ->get();

                foreach ($attendanceRecords as $ar) {
                    DB::table('group_guest_attendance')->insert([
                        'group_guest_id' => $guestId,
                        'attendance_id' => $ar->attendance_id,
                        'present' => $ar->present ?? true,
                        'created_at' => $ar->created_at,
                        'updated_at' => $ar->updated_at,
                    ]);
                }
            }

            // Remove guest from group_person pivot
            DB::table('group_person')
                ->where('person_id', $person->id)
                ->where('role', 'guest')
                ->delete();

            // Remove attendance records for this person
            DB::table('attendance_records')
                ->where('person_id', $person->id)
                ->delete();

            // Delete the Person record (they were only a guest, not a real member)
            DB::table('people')->where('id', $person->id)->delete();
        }
    }

    public function down(): void
    {
        // Migrate group_guests back to people
        $guests = DB::table('group_guests')->whereNull('deleted_at')->get();

        foreach ($guests as $guest) {
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

            DB::table('group_person')->insert([
                'group_id' => $guest->group_id,
                'person_id' => $personId,
                'role' => 'guest',
                'joined_at' => $guest->created_at ? date('Y-m-d', strtotime($guest->created_at)) : null,
                'created_at' => $guest->created_at,
                'updated_at' => $guest->updated_at,
            ]);

            // Migrate attendance back
            $guestAttendances = DB::table('group_guest_attendance')
                ->where('group_guest_id', $guest->id)
                ->get();

            foreach ($guestAttendances as $ga) {
                DB::table('attendance_records')->insert([
                    'attendance_id' => $ga->attendance_id,
                    'person_id' => $personId,
                    'present' => $ga->present,
                    'created_at' => $ga->created_at,
                    'updated_at' => $ga->updated_at,
                ]);
            }
        }

        Schema::dropIfExists('group_guest_attendance');
        Schema::dropIfExists('group_guests');
    }
};
