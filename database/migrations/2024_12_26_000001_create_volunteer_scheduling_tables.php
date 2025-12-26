<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Blockout Dates - коли волонтер недоступний
        if (!Schema::hasTable('blockout_dates')) {
            Schema::create('blockout_dates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('person_id')->constrained()->onDelete('cascade');
                $table->foreignId('church_id')->constrained()->onDelete('cascade');
                $table->date('start_date');
                $table->date('end_date');
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->boolean('all_day')->default(true);
                $table->enum('reason', ['vacation', 'travel', 'sick', 'family', 'work', 'other'])->default('other');
                $table->string('reason_note')->nullable();
                $table->boolean('applies_to_all')->default(true);
                $table->enum('recurrence', ['none', 'weekly', 'biweekly', 'monthly', 'custom'])->default('none');
                $table->json('recurrence_config')->nullable();
                $table->date('recurrence_end_date')->nullable();
                $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
                $table->timestamps();
                $table->index(['person_id', 'start_date', 'end_date']);
                $table->index(['church_id', 'start_date']);
            });
        }

        // Blockout-Ministry pivot
        if (!Schema::hasTable('blockout_date_ministry')) {
            Schema::create('blockout_date_ministry', function (Blueprint $table) {
                $table->id();
                $table->foreignId('blockout_date_id')->constrained()->onDelete('cascade');
                $table->foreignId('ministry_id')->constrained()->onDelete('cascade');
                $table->unique(['blockout_date_id', 'ministry_id']);
            });
        }

        // Scheduling Preferences
        if (!Schema::hasTable('scheduling_preferences')) {
            Schema::create('scheduling_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('person_id')->constrained()->onDelete('cascade');
                $table->foreignId('church_id')->constrained()->onDelete('cascade');
                $table->unsignedTinyInteger('max_times_per_month')->nullable();
                $table->unsignedTinyInteger('preferred_times_per_month')->nullable();
                $table->foreignId('prefer_with_person_id')->nullable()->constrained('people')->onDelete('set null');
                $table->enum('household_preference', ['none', 'together', 'separate'])->default('none');
                $table->timestamp('last_blockout_request_sent_at')->nullable();
                $table->timestamp('last_blockout_response_at')->nullable();
                $table->text('scheduling_notes')->nullable();
                $table->timestamps();
                $table->unique(['person_id', 'church_id']);
            });
        }

        // Ministry-specific preferences
        if (!Schema::hasTable('ministry_preferences')) {
            Schema::create('ministry_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('scheduling_preference_id')->constrained()->onDelete('cascade');
                $table->foreignId('ministry_id')->constrained()->onDelete('cascade');
                $table->unsignedTinyInteger('max_times_per_month')->nullable();
                $table->unsignedTinyInteger('preferred_times_per_month')->nullable();
                $table->unique(['scheduling_preference_id', 'ministry_id']);
            });
        }

        // Position-specific preferences
        if (!Schema::hasTable('position_preferences')) {
            Schema::create('position_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('scheduling_preference_id')->constrained()->onDelete('cascade');
                $table->foreignId('position_id')->constrained()->onDelete('cascade');
                $table->unsignedTinyInteger('max_times_per_month')->nullable();
                $table->unsignedTinyInteger('preferred_times_per_month')->nullable();
                $table->unique(['scheduling_preference_id', 'position_id']);
            });
        }

        // Add tracking fields to people table
        Schema::table('people', function (Blueprint $table) {
            if (!Schema::hasColumn('people', 'last_scheduled_at')) {
                $table->timestamp('last_scheduled_at')->nullable();
            }
            if (!Schema::hasColumn('people', 'times_scheduled_this_month')) {
                $table->unsignedInteger('times_scheduled_this_month')->default(0);
            }
            if (!Schema::hasColumn('people', 'times_scheduled_this_year')) {
                $table->unsignedInteger('times_scheduled_this_year')->default(0);
            }
        });

        // Add tracking fields to assignments table
        Schema::table('assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('assignments', 'email_sent_at')) {
                $table->timestamp('email_sent_at')->nullable();
            }
            if (!Schema::hasColumn('assignments', 'email_opened_at')) {
                $table->timestamp('email_opened_at')->nullable();
            }
            if (!Schema::hasColumn('assignments', 'responded_at')) {
                $table->timestamp('responded_at')->nullable();
            }
            if (!Schema::hasColumn('assignments', 'blockout_override')) {
                $table->boolean('blockout_override')->default(false);
            }
            if (!Schema::hasColumn('assignments', 'preference_override')) {
                $table->boolean('preference_override')->default(false);
            }
            if (!Schema::hasColumn('assignments', 'conflict_override')) {
                $table->boolean('conflict_override')->default(false);
            }
            if (!Schema::hasColumn('assignments', 'decline_reason')) {
                $table->string('decline_reason')->nullable();
            }
            if (!Schema::hasColumn('assignments', 'assignment_notes')) {
                $table->text('assignment_notes')->nullable();
            }
        });

        // Scheduling conflicts log
        if (!Schema::hasTable('scheduling_conflicts')) {
            Schema::create('scheduling_conflicts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assignment_id')->constrained()->onDelete('cascade');
                $table->enum('conflict_type', [
                    'blockout',
                    'concurrent',
                    'preference_limit',
                    'max_limit',
                    'household',
                ]);
                $table->string('conflict_details')->nullable();
                $table->boolean('was_overridden')->default(false);
                $table->foreignId('overridden_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
                $table->index(['assignment_id', 'conflict_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduling_conflicts');

        Schema::table('assignments', function (Blueprint $table) {
            $columns = ['email_sent_at', 'email_opened_at', 'responded_at', 'blockout_override',
                        'preference_override', 'conflict_override', 'decline_reason', 'assignment_notes'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('assignments', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('people', function (Blueprint $table) {
            $columns = ['last_scheduled_at', 'times_scheduled_this_month', 'times_scheduled_this_year'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('people', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::dropIfExists('position_preferences');
        Schema::dropIfExists('ministry_preferences');
        Schema::dropIfExists('scheduling_preferences');
        Schema::dropIfExists('blockout_date_ministry');
        Schema::dropIfExists('blockout_dates');
    }
};
