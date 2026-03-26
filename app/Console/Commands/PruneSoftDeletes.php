<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PruneSoftDeletes extends Command
{
    protected $signature = 'db:prune-soft-deletes {--days=90 : Days after which soft-deleted records are permanently removed}';

    protected $description = 'Permanently delete soft-deleted records older than specified days';

    /**
     * Tables with soft deletes and their model classes.
     * Only includes tables where permanent deletion is safe.
     */
    protected array $tables = [
        'audit_logs' => 180,       // Keep audit logs longer
        'assignments' => 90,
        'notifications' => 60,
    ];

    public function handle(): int
    {
        $defaultDays = (int) $this->option('days');
        $totalDeleted = 0;

        foreach ($this->tables as $table => $days) {
            $days = $days ?? $defaultDays;
            $cutoff = now()->subDays($days);

            $count = DB::table($table)
                ->whereNotNull('deleted_at')
                ->where('deleted_at', '<', $cutoff)
                ->count();

            if ($count > 0) {
                DB::table($table)
                    ->whereNotNull('deleted_at')
                    ->where('deleted_at', '<', $cutoff)
                    ->delete();

                $this->line("  {$table}: permanently deleted {$count} records (>{$days} days)");
                $totalDeleted += $count;
            }
        }

        if ($totalDeleted === 0) {
            $this->info('No old soft-deleted records to prune.');
        } else {
            $this->info("Total: permanently deleted {$totalDeleted} records.");
            Log::info("PruneSoftDeletes: permanently deleted {$totalDeleted} records");
        }

        return self::SUCCESS;
    }
}
