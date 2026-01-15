<?php

namespace App\Console\Commands;

use App\Models\Church;
use App\Services\PrivatbankService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncPrivatbankTransactions extends Command
{
    protected $signature = 'privatbank:sync {--days=7 : Number of days to sync}';

    protected $description = 'Sync PrivatBank transactions for churches with auto-sync enabled';

    public function handle(): int
    {
        $churches = Church::where('privatbank_auto_sync', true)
            ->whereNotNull('privatbank_merchant_id')
            ->whereNotNull('privatbank_card_number')
            ->get();

        if ($churches->isEmpty()) {
            $this->info('No churches with PrivatBank auto-sync enabled');
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
                $service = new PrivatbankService($church);

                if (!$service->isConfigured()) {
                    $this->warn("  Credentials not configured, skipping");
                    continue;
                }

                $result = $service->syncTransactions($days);

                if ($result['error']) {
                    $this->error("  Error: {$result['error']}");
                    $errors++;
                    Log::warning("PrivatBank auto-sync failed for church {$church->id}", [
                        'error' => $result['error'],
                    ]);
                } else {
                    $this->info("  Imported: {$result['imported']}, Skipped: {$result['skipped']}");
                    $totalImported += $result['imported'];
                    $totalSkipped += $result['skipped'];
                }

                // Small delay between churches to be nice to the API
                if ($churches->count() > 1) {
                    sleep(5);
                }
            } catch (\Exception $e) {
                $this->error("  Exception: {$e->getMessage()}");
                $errors++;
                Log::error("PrivatBank auto-sync exception for church {$church->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info("Summary: {$totalImported} imported, {$totalSkipped} skipped, {$errors} errors");

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
