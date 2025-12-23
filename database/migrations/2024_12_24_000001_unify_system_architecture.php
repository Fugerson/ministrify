<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================
        // 1. UNIFIED ATTENDANCE SYSTEM
        // ============================================

        // Add polymorphic fields to attendances
        Schema::table('attendances', function (Blueprint $table) {
            // Polymorphic relation (event, group, meeting, etc.)
            $table->string('attendable_type')->nullable()->after('church_id');
            $table->unsignedBigInteger('attendable_id')->nullable()->after('attendable_type');

            // Additional fields from group_attendances
            $table->time('time')->nullable()->after('date');
            $table->string('location')->nullable()->after('time');
            $table->integer('members_present')->default(0)->after('total_count');
            $table->integer('guests_count')->default(0)->after('members_present');
            $table->foreignId('recorded_by')->nullable()->after('guests_count')->constrained('users')->nullOnDelete();

            // Type for filtering (service, group, meeting, event)
            $table->string('type')->default('service')->after('attendable_id');

            $table->index(['attendable_type', 'attendable_id']);
            $table->index('type');
        });

        // Add extra fields to attendance_records
        Schema::table('attendance_records', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_records', 'checked_in_at')) {
                $table->time('checked_in_at')->nullable()->after('present');
            }
            if (!Schema::hasColumn('attendance_records', 'notes')) {
                $table->text('notes')->nullable()->after('checked_in_at');
            }
        });

        // Migrate existing event-based attendance to polymorphic
        DB::table('attendances')
            ->whereNotNull('event_id')
            ->update([
                'attendable_type' => 'App\\Models\\Event',
                'attendable_id' => DB::raw('event_id'),
                'type' => 'event',
            ]);

        // Migrate group_attendances to attendances table
        $groupAttendances = DB::table('group_attendances')->get();
        foreach ($groupAttendances as $ga) {
            $attendanceId = DB::table('attendances')->insertGetId([
                'church_id' => $ga->church_id ?? DB::table('groups')->where('id', $ga->group_id)->value('church_id'),
                'attendable_type' => 'App\\Models\\Group',
                'attendable_id' => $ga->group_id,
                'type' => 'group',
                'date' => $ga->date,
                'time' => $ga->time ?? null,
                'location' => $ga->location ?? null,
                'total_count' => $ga->total_count ?? 0,
                'members_present' => $ga->members_present ?? 0,
                'guests_count' => $ga->guests_count ?? 0,
                'recorded_by' => $ga->recorded_by ?? null,
                'notes' => $ga->notes,
                'created_at' => $ga->created_at,
                'updated_at' => $ga->updated_at,
            ]);

            // Migrate records
            $records = DB::table('group_attendance_records')
                ->where('group_attendance_id', $ga->id)
                ->get();

            foreach ($records as $record) {
                DB::table('attendance_records')->insert([
                    'attendance_id' => $attendanceId,
                    'person_id' => $record->person_id,
                    'present' => $record->present,
                    'checked_in_at' => $record->checked_in_at,
                    'notes' => $record->notes,
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at,
                ]);
            }
        }

        // ============================================
        // 2. SIMPLIFIED PEOPLE ROLES
        // ============================================

        Schema::table('people', function (Blueprint $table) {
            // Membership status: guest -> newcomer -> member -> active
            if (!Schema::hasColumn('people', 'membership_status')) {
                $table->string('membership_status')->default('member')->after('church_role');
            }

            // Baptism date
            if (!Schema::hasColumn('people', 'baptism_date')) {
                $table->date('baptism_date')->nullable()->after('joined_date');
            }

            // First visit date
            if (!Schema::hasColumn('people', 'first_visit_date')) {
                $table->date('first_visit_date')->nullable()->after('joined_date');
            }
        });

        // Set membership_status based on existing data
        // People with ministries are 'active'
        DB::table('people')
            ->whereIn('id', function ($query) {
                $query->select('person_id')->from('ministry_person');
            })
            ->update(['membership_status' => 'active']);

        // ============================================
        // 3. UNIFIED TRANSACTIONS (Income + Donation)
        // ============================================

        // Create transaction_categories FIRST (before transactions)
        Schema::create('transaction_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('type'); // income, expense, both
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_tithe')->default(false);
            $table->boolean('is_offering')->default(false);
            $table->boolean('is_donation')->default(false);
            $table->boolean('is_system')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Migrate income_categories to transaction_categories BEFORE creating transactions
        if (Schema::hasTable('income_categories')) {
            $incomeCategories = DB::table('income_categories')->get();
            foreach ($incomeCategories as $cat) {
                DB::table('transaction_categories')->insert([
                    'church_id' => $cat->church_id,
                    'name' => $cat->name,
                    'type' => 'income',
                    'icon' => $cat->icon ?? null,
                    'color' => $cat->color ?? null,
                    'is_tithe' => $cat->is_tithe ?? false,
                    'is_offering' => $cat->is_offering ?? false,
                    'is_donation' => $cat->is_donation ?? false,
                    'sort_order' => $cat->sort_order ?? 0,
                    'created_at' => $cat->created_at ?? now(),
                    'updated_at' => $cat->updated_at ?? now(),
                ]);
            }
        }

        // Migrate expense_categories to transaction_categories
        if (Schema::hasTable('expense_categories')) {
            $expenseCategories = DB::table('expense_categories')->get();
            foreach ($expenseCategories as $cat) {
                DB::table('transaction_categories')->insert([
                    'church_id' => $cat->church_id,
                    'name' => $cat->name,
                    'type' => 'expense',
                    'created_at' => $cat->created_at ?? now(),
                    'updated_at' => $cat->updated_at ?? now(),
                ]);
            }
        }

        // Now create transactions table
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');

            // Type: income or expense
            $table->enum('direction', ['in', 'out'])->default('in');

            // Source type: tithe, offering, donation, expense, transfer
            $table->string('source_type');

            // Amount
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('UAH');

            // Date
            $table->date('date');

            // Category (unified - replaces income_category and expense_category)
            $table->foreignId('category_id')->nullable()->constrained('transaction_categories')->nullOnDelete();

            // Related entities
            $table->foreignId('person_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ministry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('donation_campaigns')->nullOnDelete();

            // Donor info (for anonymous or external donations)
            $table->string('donor_name')->nullable();
            $table->string('donor_email')->nullable();
            $table->string('donor_phone')->nullable();
            $table->boolean('is_anonymous')->default(false);

            // Payment details
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('order_id')->nullable();
            $table->json('payment_data')->nullable();

            // Status
            $table->string('status')->default('completed');

            // Description and notes
            $table->string('description')->nullable();
            $table->text('notes')->nullable();
            $table->text('purpose')->nullable();

            // Recorded by
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();

            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['church_id', 'date']);
            $table->index(['church_id', 'direction']);
            $table->index('source_type');
            $table->index('status');
        });

        // Migrate incomes to transactions
        if (Schema::hasTable('incomes')) {
            $incomes = DB::table('incomes')->get();
            foreach ($incomes as $income) {
                // Find new category_id by looking up the old category name
                $oldCategoryName = DB::table('income_categories')
                    ->where('id', $income->category_id)
                    ->value('name');

                $newCategoryId = DB::table('transaction_categories')
                    ->where('church_id', $income->church_id)
                    ->where('type', 'income')
                    ->where('name', $oldCategoryName)
                    ->value('id');

                DB::table('transactions')->insert([
                    'church_id' => $income->church_id,
                    'direction' => 'in',
                    'source_type' => 'income',
                    'amount' => $income->amount,
                    'date' => $income->date,
                    'category_id' => $newCategoryId,
                    'person_id' => $income->person_id,
                    'is_anonymous' => $income->is_anonymous ?? false,
                    'payment_method' => $income->payment_method,
                    'status' => 'completed',
                    'description' => $income->description,
                    'notes' => $income->notes,
                    'recorded_by' => $income->user_id,
                    'created_at' => $income->created_at,
                    'updated_at' => $income->updated_at,
                ]);
            }
        }

        // Migrate donations to transactions
        if (Schema::hasTable('donations')) {
            $donations = DB::table('donations')->get();
            foreach ($donations as $donation) {
                DB::table('transactions')->insert([
                    'church_id' => $donation->church_id,
                    'direction' => 'in',
                    'source_type' => 'donation',
                    'amount' => $donation->amount,
                    'currency' => $donation->currency ?? 'UAH',
                    'date' => $donation->paid_at ?? $donation->created_at,
                    'person_id' => $donation->person_id,
                    'ministry_id' => $donation->ministry_id,
                    'campaign_id' => $donation->campaign_id,
                    'donor_name' => $donation->donor_name,
                    'donor_email' => $donation->donor_email,
                    'donor_phone' => $donation->donor_phone,
                    'is_anonymous' => $donation->is_anonymous ?? false,
                    'payment_method' => $donation->payment_method,
                    'transaction_id' => $donation->transaction_id,
                    'order_id' => $donation->order_id,
                    'payment_data' => $donation->payment_data,
                    'status' => $donation->status ?? 'completed',
                    'purpose' => $donation->purpose,
                    'notes' => $donation->notes,
                    'paid_at' => $donation->paid_at,
                    'created_at' => $donation->created_at,
                    'updated_at' => $donation->updated_at,
                ]);
            }
        }

        // Migrate expenses to transactions
        if (Schema::hasTable('expenses')) {
            $expenses = DB::table('expenses')->get();
            foreach ($expenses as $expense) {
                $oldCategoryName = DB::table('expense_categories')
                    ->where('id', $expense->category_id)
                    ->value('name');

                $newCategoryId = DB::table('transaction_categories')
                    ->where('church_id', $expense->church_id)
                    ->where('type', 'expense')
                    ->where('name', $oldCategoryName)
                    ->value('id');

                DB::table('transactions')->insert([
                    'church_id' => $expense->church_id,
                    'direction' => 'out',
                    'source_type' => 'expense',
                    'amount' => $expense->amount,
                    'date' => $expense->date,
                    'category_id' => $newCategoryId,
                    'ministry_id' => $expense->ministry_id,
                    'payment_method' => $expense->payment_method ?? null,
                    'status' => 'completed',
                    'description' => $expense->description,
                    'notes' => $expense->notes,
                    'recorded_by' => $expense->user_id ?? null,
                    'created_at' => $expense->created_at,
                    'updated_at' => $expense->updated_at,
                ]);
            }
        }

        // ============================================
        // 4. EVENT TASK TEMPLATES
        // ============================================

        Schema::create('event_task_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')->constrained()->onDelete('cascade');
            $table->foreignId('ministry_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('tasks'); // Array of task definitions
            $table->boolean('auto_create')->default(true);
            $table->integer('days_before')->default(7); // Create tasks X days before event
            $table->timestamps();
        });

        // Add template reference to events
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'task_template_id')) {
                $table->foreignId('task_template_id')->nullable()->after('ministry_id')
                    ->constrained('event_task_templates')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // Remove event template reference
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['task_template_id']);
            $table->dropColumn('task_template_id');
        });

        Schema::dropIfExists('event_task_templates');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('transaction_categories');

        Schema::table('people', function (Blueprint $table) {
            $table->dropColumn(['membership_status', 'baptism_date', 'first_visit_date']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['recorded_by']);
            $table->dropIndex(['attendable_type', 'attendable_id']);
            $table->dropIndex(['type']);
            $table->dropColumn([
                'attendable_type', 'attendable_id', 'type',
                'time', 'location', 'members_present', 'guests_count', 'recorded_by'
            ]);
        });

        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropColumn(['checked_in_at', 'notes']);
        });
    }
};
