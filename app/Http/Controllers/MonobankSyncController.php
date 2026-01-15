<?php

namespace App\Http\Controllers;

use App\Models\MonobankTransaction;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\Person;
use App\Services\MonobankPersonalService;
use Illuminate\Http\Request;

class MonobankSyncController extends Controller
{
    /**
     * Show Monobank integration page
     */
    public function index()
    {
        $church = $this->getCurrentChurch();
        $service = new MonobankPersonalService($church);

        $isConnected = $service->isConfigured();
        $accounts = [];
        $clientName = null;

        if ($isConnected) {
            $info = $service->getClientInfo();
            if ($info) {
                $clientName = $info['name'] ?? null;
                $accounts = $service->getUahAccounts();
            }
        }

        // Get recent transactions
        $transactions = MonobankTransaction::where('church_id', $church->id)
            ->orderByDesc('mono_time')
            ->limit(50)
            ->get();

        // Stats
        $stats = [
            'total' => MonobankTransaction::where('church_id', $church->id)->count(),
            'income' => MonobankTransaction::where('church_id', $church->id)->income()->count(),
            'unprocessed' => MonobankTransaction::where('church_id', $church->id)->unprocessedIncome()->count(),
            'last_sync' => $church->monobank_last_sync,
        ];

        // Get donation category for quick import (prefer donation category, then any income)
        $donationCategory = TransactionCategory::where('church_id', $church->id)
            ->where('type', 'income')
            ->orderByDesc('is_donation')
            ->first();

        return view('finances.monobank.index', compact(
            'isConnected',
            'clientName',
            'accounts',
            'transactions',
            'stats',
            'church',
            'donationCategory'
        ));
    }

    /**
     * Show setup page (redirects to index)
     */
    public function setup()
    {
        return redirect()->route('finances.monobank.index');
    }

    /**
     * Connect Monobank (save token)
     */
    public function connect(Request $request)
    {
        $request->validate([
            'token' => 'required|string|min:10',
            'account_id' => 'nullable|string',
        ]);

        $church = $this->getCurrentChurch();
        $service = new MonobankPersonalService();

        // Validate token
        $validation = $service->validateToken($request->token);

        if (!$validation) {
            return back()->withErrors(['token' => 'Невірний токен або помилка з\'єднання з Monobank']);
        }

        // Save token
        $accountId = $request->account_id;

        // If no account selected, use first UAH account
        if (!$accountId && !empty($validation['accounts'])) {
            $accountId = $validation['accounts'][0]['id'];
        }

        $service->saveToken($church, $request->token, $accountId);

        return redirect()->route('monobank.index')
            ->with('success', "Monobank підключено! Ім'я: {$validation['name']}");
    }

    /**
     * Select account
     */
    public function selectAccount(Request $request)
    {
        $request->validate([
            'account_id' => 'required|string',
        ]);

        $church = $this->getCurrentChurch();
        $church->update(['monobank_account_id' => $request->account_id]);

        return back()->with('success', 'Рахунок обрано');
    }

    /**
     * Disconnect Monobank
     */
    public function disconnect()
    {
        $church = $this->getCurrentChurch();
        $service = new MonobankPersonalService();
        $service->disconnect($church);

        return redirect()->route('monobank.index')
            ->with('success', 'Monobank відключено');
    }

    /**
     * Sync transactions
     */
    public function sync(Request $request)
    {
        $church = $this->getCurrentChurch();
        $service = new MonobankPersonalService($church);

        $days = $request->get('days', 7);
        $result = $service->syncTransactions($days);

        if ($result['error']) {
            return back()->with('error', $result['error']);
        }

        $message = "Синхронізовано: {$result['imported']} нових транзакцій";
        if ($result['skipped'] > 0) {
            $message .= " ({$result['skipped']} вже існували)";
        }

        return back()->with('success', $message);
    }

    /**
     * Import transaction as donation
     */
    public function import(Request $request, MonobankTransaction $monoTransaction)
    {
        $church = $this->getCurrentChurch();

        // Verify belongs to church
        if ($monoTransaction->church_id !== $church->id) {
            abort(403);
        }

        // Already processed?
        if ($monoTransaction->is_processed) {
            return back()->with('error', 'Транзакція вже оброблена');
        }

        $request->validate([
            'category_id' => 'required|exists:transaction_categories,id',
            'person_id' => 'nullable|exists:people,id',
            'description' => 'nullable|string|max:500',
        ]);

        // Create transaction
        $transaction = Transaction::create([
            'church_id' => $church->id,
            'type' => 'income',
            'amount' => $monoTransaction->amount_uah,
            'category_id' => $request->category_id,
            'person_id' => $request->person_id,
            'description' => $request->description ?: $monoTransaction->counterpart_display,
            'date' => $monoTransaction->mono_time->toDateString(),
            'status' => Transaction::STATUS_COMPLETED,
            'payment_method' => 'monobank',
        ]);

        // Mark as processed
        $monoTransaction->update([
            'is_processed' => true,
            'transaction_id' => $transaction->id,
            'person_id' => $request->person_id,
        ]);

        return back()->with('success', 'Транзакцію імпортовано як пожертву');
    }

    /**
     * Ignore transaction
     */
    public function ignore(MonobankTransaction $monoTransaction)
    {
        $church = $this->getCurrentChurch();

        if ($monoTransaction->church_id !== $church->id) {
            abort(403);
        }

        $monoTransaction->update(['is_ignored' => true]);

        return back()->with('success', 'Транзакцію приховано');
    }

    /**
     * Restore ignored transaction
     */
    public function restore(MonobankTransaction $monoTransaction)
    {
        $church = $this->getCurrentChurch();

        if ($monoTransaction->church_id !== $church->id) {
            abort(403);
        }

        $monoTransaction->update(['is_ignored' => false]);

        return back()->with('success', 'Транзакцію відновлено');
    }

    /**
     * Bulk import selected transactions
     */
    public function bulkImport(Request $request)
    {
        $church = $this->getCurrentChurch();

        $request->validate([
            'transaction_ids' => 'required|array|min:1',
            'transaction_ids.*' => 'integer',
            'category_id' => 'required|exists:transaction_categories,id',
        ]);

        $imported = 0;

        foreach ($request->transaction_ids as $id) {
            $monoTx = MonobankTransaction::where('id', $id)
                ->where('church_id', $church->id)
                ->where('is_processed', false)
                ->where('is_income', true)
                ->first();

            if (!$monoTx) continue;

            $transaction = Transaction::create([
                'church_id' => $church->id,
                'type' => 'income',
                'amount' => $monoTx->amount_uah,
                'category_id' => $request->category_id,
                'description' => $monoTx->counterpart_display,
                'date' => $monoTx->mono_time->toDateString(),
                'status' => Transaction::STATUS_COMPLETED,
                'payment_method' => 'monobank',
            ]);

            $monoTx->update([
                'is_processed' => true,
                'transaction_id' => $transaction->id,
            ]);

            $imported++;
        }

        return back()->with('success', "Імпортовано {$imported} транзакцій");
    }

    /**
     * API: Get transactions for AJAX
     */
    public function getTransactions(Request $request)
    {
        $church = $this->getCurrentChurch();

        $query = MonobankTransaction::where('church_id', $church->id);

        if ($request->get('income_only')) {
            $query->income();
        }

        if ($request->get('unprocessed_only')) {
            $query->unprocessedIncome();
        }

        $transactions = $query->orderByDesc('mono_time')->limit(100)->get();

        return response()->json($transactions);
    }
}
