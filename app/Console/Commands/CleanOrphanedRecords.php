<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanOrphanedRecords extends Command
{
    protected $signature = 'db:clean-orphaned';

    protected $description = 'Remove orphaned records from pivot tables and related data';

    public function handle(): int
    {
        $totalCleaned = 0;

        // Push subscriptions for deleted users
        $count = DB::table('push_subscriptions')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.id', 'push_subscriptions.user_id');
            })
            ->count();

        if ($count > 0) {
            DB::table('push_subscriptions')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('users')
                        ->whereColumn('users.id', 'push_subscriptions.user_id');
                })
                ->delete();
            $this->line("  push_subscriptions: cleaned {$count} orphaned records");
            $totalCleaned += $count;
        }

        // Expired push subscriptions (inactive for 90+ days)
        $count = DB::table('push_subscriptions')
            ->where('updated_at', '<', now()->subDays(90))
            ->count();

        if ($count > 0) {
            DB::table('push_subscriptions')
                ->where('updated_at', '<', now()->subDays(90))
                ->delete();
            $this->line("  push_subscriptions: cleaned {$count} stale records (>90 days)");
            $totalCleaned += $count;
        }

        if ($totalCleaned === 0) {
            $this->info('No orphaned records found.');
        } else {
            $this->info("Total: cleaned {$totalCleaned} orphaned records.");
            Log::info("CleanOrphanedRecords: cleaned {$totalCleaned} records");
        }

        return self::SUCCESS;
    }
}
