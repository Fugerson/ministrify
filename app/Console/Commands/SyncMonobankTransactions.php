<?php

namespace App\Console\Commands;

use App\Models\Church;
use App\Services\MonobankPersonalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncMonobankTransactions extends Command
{
    protected $signature = 'monobank:sync {--days=7 : Number of days to sync}';

    protected $description = 'Sync Monobank transactions for churches with auto-sync enabled';

    public function handle(): int
    {
        $churches = Church::where('monobank_auto_sync', true)
            ->whereNotNull('monobank_token')
            ->get();

        if ($churches->isEmpty()) {
            $this->info('No churches with auto-sync enabled');
            return self::SUCCESS;
        }

        $this->info("Syncing {$churches->count()} church(es)...");

        $days = (int) $this->option('days');
        $totalImported = 0;
        $totalSkipped = 0;
        $errors = 0;

        foreach ($churches as $church) {
            $this->line("- {$church->name}...");

            try {
                $service = new MonobankPersonalService($church);

                if (!$service->isConfigured()) {
                    $this->warn("  Token not configured, skipping");
                    continue;
                }

                $result = $service->syncTransactions($days);

                if ($result['error']) {
                    $this->error("  Error: {$result['error']}");
                    $errors++;
                    Log::warning("Monobank auto-sync failed for church {$church->id}", [
                        'error' => $result['error'],
                    ]);
                } else {
                    $this->info("  Imported: {$result['imported']}, Skipped: {$result['skipped']}");
                    $totalImported += $result['imported'];
                    $totalSkipped += $result['skipped'];
                }

                // Respect Monobank rate limits (1 request per minute)
                if ($churches->count() > 1) {
                    sleep(60);
                }
            } catch (\Exception $e) {
                $this->error("  Exception: {$e->getMessage()}");
                $errors++;
                Log::error("Monobank auto-sync exception for church {$church->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info("Summary: {$totalImported} imported, {$totalSkipped} skipped, {$errors} errors");

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
