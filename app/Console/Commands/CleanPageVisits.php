<?php

namespace App\Console\Commands;

use App\Models\PageVisit;
use Illuminate\Console\Command;

class CleanPageVisits extends Command
{
    protected $signature = 'page-visits:clean {--days=30 : Days to keep}';

    protected $description = 'Delete page visit records older than specified days';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $count = PageVisit::where('created_at', '<', $cutoff)->count();

        if ($count === 0) {
            $this->info('No old page visits to clean.');
            return self::SUCCESS;
        }

        PageVisit::where('created_at', '<', $cutoff)->delete();

        $this->info("Deleted {$count} page visits older than {$days} days.");

        return self::SUCCESS;
    }
}
