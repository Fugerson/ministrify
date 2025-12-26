<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds indexes to improve query performance on frequently accessed columns.
     */
    public function up(): void
    {
        // Assignments table - critical for event/person lookups
        Schema::table('assignments', function (Blueprint $table) {
            if (!$this->hasIndex('assignments', 'assignments_event_id_index')) {
                $table->index('event_id');
            }
            if (!$this->hasIndex('assignments', 'assignments_person_id_index')) {
                $table->index('person_id');
            }
            if (!$this->hasIndex('assignments', 'assignments_position_id_index')) {
                $table->index('position_id');
            }
            if (!$this->hasIndex('assignments', 'assignments_status_index')) {
                $table->index('status');
            }
        });

        // Ministries table
        Schema::table('ministries', function (Blueprint $table) {
            if (!$this->hasIndex('ministries', 'ministries_church_id_index')) {
                $table->index('church_id');
            }
            if (!$this->hasIndex('ministries', 'ministries_leader_id_index')) {
                $table->index('leader_id');
            }
        });

        // Events table - ministry lookups
        Schema::table('events', function (Blueprint $table) {
            if (!$this->hasIndex('events', 'events_ministry_id_index')) {
                $table->index('ministry_id');
            }
        });

        // People table - created_at for filtering
        Schema::table('people', function (Blueprint $table) {
            if (!$this->hasIndex('people', 'people_created_at_index')) {
                $table->index('created_at');
            }
            if (!$this->hasIndex('people', 'people_user_id_index') && Schema::hasColumn('people', 'user_id')) {
                $table->index('user_id');
            }
        });

        // Transactions table - category lookups
        Schema::table('transactions', function (Blueprint $table) {
            if (!$this->hasIndex('transactions', 'transactions_category_id_index') && Schema::hasColumn('transactions', 'category_id')) {
                $table->index('category_id');
            }
            if (!$this->hasIndex('transactions', 'transactions_ministry_id_index') && Schema::hasColumn('transactions', 'ministry_id')) {
                $table->index('ministry_id');
            }
            if (!$this->hasIndex('transactions', 'transactions_type_index') && Schema::hasColumn('transactions', 'type')) {
                $table->index('type');
            }
        });

        // Groups table
        Schema::table('groups', function (Blueprint $table) {
            if (!$this->hasIndex('groups', 'groups_leader_id_index')) {
                $table->index('leader_id');
            }
        });

        // Group_person pivot - person lookups
        if (Schema::hasTable('group_person')) {
            Schema::table('group_person', function (Blueprint $table) {
                if (!$this->hasIndex('group_person', 'group_person_person_id_index')) {
                    $table->index('person_id');
                }
            });
        }

        // Ministry_person pivot - person lookups
        if (Schema::hasTable('ministry_person')) {
            Schema::table('ministry_person', function (Blueprint $table) {
                if (!$this->hasIndex('ministry_person', 'ministry_person_person_id_index')) {
                    $table->index('person_id');
                }
            });
        }

        // Attendances table
        Schema::table('attendances', function (Blueprint $table) {
            if (!$this->hasIndex('attendances', 'attendances_event_id_index') && Schema::hasColumn('attendances', 'event_id')) {
                $table->index('event_id');
            }
        });

        // Attendance_records - person lookups
        if (Schema::hasTable('attendance_records')) {
            Schema::table('attendance_records', function (Blueprint $table) {
                if (!$this->hasIndex('attendance_records', 'attendance_records_person_id_index')) {
                    $table->index('person_id');
                }
            });
        }

        // Board cards - column and assigned_to lookups
        if (Schema::hasTable('board_cards')) {
            Schema::table('board_cards', function (Blueprint $table) {
                if (!$this->hasIndex('board_cards', 'board_cards_column_id_index')) {
                    $table->index('column_id');
                }
                if (!$this->hasIndex('board_cards', 'board_cards_assigned_to_index') && Schema::hasColumn('board_cards', 'assigned_to')) {
                    $table->index('assigned_to');
                }
            });
        }

        // Board card comments
        if (Schema::hasTable('board_card_comments')) {
            Schema::table('board_card_comments', function (Blueprint $table) {
                if (!$this->hasIndex('board_card_comments', 'board_card_comments_card_id_index')) {
                    $table->index('card_id');
                }
            });
        }

        // Service plan items
        if (Schema::hasTable('service_plan_items')) {
            Schema::table('service_plan_items', function (Blueprint $table) {
                if (!$this->hasIndex('service_plan_items', 'service_plan_items_event_id_index')) {
                    $table->index('event_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropIndex(['event_id']);
            $table->dropIndex(['person_id']);
            $table->dropIndex(['position_id']);
            $table->dropIndex(['status']);
        });

        Schema::table('ministries', function (Blueprint $table) {
            $table->dropIndex(['church_id']);
            $table->dropIndex(['leader_id']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['ministry_id']);
        });

        Schema::table('people', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            if (Schema::hasColumn('people', 'user_id')) {
                $table->dropIndex(['user_id']);
            }
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['category_id']);
            if (Schema::hasColumn('transactions', 'ministry_id')) {
                $table->dropIndex(['ministry_id']);
            }
            $table->dropIndex(['type']);
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropIndex(['leader_id']);
        });

        if (Schema::hasTable('group_person')) {
            Schema::table('group_person', function (Blueprint $table) {
                $table->dropIndex(['person_id']);
            });
        }

        if (Schema::hasTable('ministry_person')) {
            Schema::table('ministry_person', function (Blueprint $table) {
                $table->dropIndex(['person_id']);
            });
        }

        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'event_id')) {
                $table->dropIndex(['event_id']);
            }
        });

        if (Schema::hasTable('attendance_records')) {
            Schema::table('attendance_records', function (Blueprint $table) {
                $table->dropIndex(['person_id']);
            });
        }

        if (Schema::hasTable('board_cards')) {
            Schema::table('board_cards', function (Blueprint $table) {
                $table->dropIndex(['column_id']);
                if (Schema::hasColumn('board_cards', 'assigned_to')) {
                    $table->dropIndex(['assigned_to']);
                }
            });
        }

        if (Schema::hasTable('board_card_comments')) {
            Schema::table('board_card_comments', function (Blueprint $table) {
                $table->dropIndex(['card_id']);
            });
        }

        if (Schema::hasTable('service_plan_items')) {
            Schema::table('service_plan_items', function (Blueprint $table) {
                $table->dropIndex(['event_id']);
            });
        }
    }

    /**
     * Check if an index exists on a table.
     */
    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = Schema::getIndexes($table);
        foreach ($indexes as $index) {
            if ($index['name'] === $indexName) {
                return true;
            }
        }
        return false;
    }
};
