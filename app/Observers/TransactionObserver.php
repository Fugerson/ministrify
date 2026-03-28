<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Services\DashboardCacheService;

class TransactionObserver
{
    public function __construct(
        private DashboardCacheService $cacheService
    ) {}

    public function created(Transaction $transaction): void
    {
        $this->clearCache($transaction);
    }

    public function updated(Transaction $transaction): void
    {
        $this->clearCache($transaction);
    }

    public function deleted(Transaction $transaction): void
    {
        $this->clearCache($transaction);
    }

    public function restored(Transaction $transaction): void
    {
        $this->clearCache($transaction);
    }

    private function clearCache(Transaction $transaction): void
    {
        if ($transaction->church_id) {
            $this->cacheService->forgetFinancialRelated($transaction->church_id);
        }
    }
}
