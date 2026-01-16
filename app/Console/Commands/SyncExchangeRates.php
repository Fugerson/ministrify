<?php

namespace App\Console\Commands;

use App\Services\NbuExchangeRateService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncExchangeRates extends Command
{
    protected $signature = 'exchange-rates:sync
                            {--date= : Specific date to sync (Y-m-d format)}
                            {--days=0 : Number of past days to backfill}';

    protected $description = 'Sync exchange rates from NBU (National Bank of Ukraine)';

    public function handle(NbuExchangeRateService $service): int
    {
        $date = $this->option('date');
        $days = (int) $this->option('days');

        if ($days > 0) {
            // Backfill mode
            $endDate = Carbon::today();
            $startDate = $endDate->copy()->subDays($days);

            $this->info("Syncing rates from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}...");

            $results = $service->syncRatesForRange($startDate, $endDate);

            $success = 0;
            $failed = 0;

            foreach ($results as $dateStr => $result) {
                if ($result['success']) {
                    $rates = collect($result['rates'])->map(fn($r) => number_format($r, 4))->implode(', ');
                    $this->line("  {$dateStr}: {$rates}");
                    $success++;
                } else {
                    $this->warn("  {$dateStr}: {$result['error']}");
                    $failed++;
                }
            }

            $this->newLine();
            $this->info("Completed: {$success} success, {$failed} failed");

            return $failed > 0 ? self::FAILURE : self::SUCCESS;
        }

        // Single date mode
        $targetDate = $date ? Carbon::parse($date) : Carbon::today();

        $this->info("Syncing exchange rates for {$targetDate->format('Y-m-d')}...");

        $result = $service->syncRates($targetDate);

        if ($result['success']) {
            $this->info('Rates synced successfully:');
            foreach ($result['rates'] as $currency => $rate) {
                $this->line("  {$currency}: " . number_format($rate, 4) . ' UAH');
            }
            return self::SUCCESS;
        }

        $this->error("Failed to sync rates: {$result['error']}");
        return self::FAILURE;
    }
}
