<?php

namespace App\Http\Controllers;

use App\Models\PrivatbankTransaction;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\Person;
use App\Services\PrivatbankService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PrivatbankSyncController extends Controller
{
    /**
     * Show PrivatBank integration page with filters
     */
    public function index(Request $request)
    {
        $church = $this->getCurrentChurch();
        $service = new PrivatbankService($church);

        $isConnected = $service->isConfigured();
        $maskedCard = null;

        if ($isConnected && $church->privatbank_card_number) {
            $card = $church->privatbank_card_number;
            $maskedCard = substr($card, 0, 4) . ' **** **** ' . substr($card, -4);
        }

        // Build query with filters
        $query = PrivatbankTransaction::where('church_id', $church->id);

        // Tab filter (status)
        $tab = $request->get('tab', 'new');
        switch ($tab) {
            case 'new':
                $query->unprocessedIncome();
                break;
            case 'imported':
                $query->where('is_processed', true);
                break;
            case 'ignored':
                $query->where('is_ignored', true);
                break;
            case 'expenses':
                $query->where('is_income', false);
                break;
            case 'all':
                // No filter
                break;
        }

        // Date filter
        if ($request->filled('date_from')) {
            $query->where('privat_time', '>=', Carbon::parse($request->date_from)->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->where('privat_time', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        // Amount filter
        if ($request->filled('amount_min')) {
            $query->whereRaw('ABS(amount) >= ?', [$request->amount_min * 100]);
        }
        if ($request->filled('amount_max')) {
            $query->whereRaw('ABS(amount) <= ?', [$request->amount_max * 100]);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('counterpart_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('terminal', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortField = $request->get('sort', 'privat_time');
        $sortDir = $request->get('dir', 'desc');

        $allowedSorts = ['privat_time', 'amount', 'counterpart_name'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'privat_time';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

        $transactions = $query->orderBy($sortField, $sortDir)->paginate(50)->withQueryString();

        // Stats
        $stats = $this->getStats($church);

        // Get categories for import
        $categories = TransactionCategory::where('church_id', $church->id)
            ->where('type', 'income')
            ->orderBy('name')
            ->get();

        $donationCategory = $categories->firstWhere('is_donation', true) ?? $categories->first();

        // Get people for import dropdown
        $people = Person::where('church_id', $church->id)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);

        return view('finances.privatbank.index', compact(
            'isConnected',
            'maskedCard',
            'transactions',
            'stats',
            'church',
            'categories',
            'donationCategory',
            'people',
            'tab',
            'sortField',
            'sortDir'
        ));
    }

    /**
     * Get statistics
     */
    protected function getStats($church): array
    {
        $baseQuery = PrivatbankTransaction::where('church_id', $church->id);

        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            'total' => (clone $baseQuery)->count(),
            'income' => (clone $baseQuery)->income()->count(),
            'unprocessed' => (clone $baseQuery)->unprocessedIncome()->count(),
            'ignored' => (clone $baseQuery)->where('is_ignored', true)->count(),
            'last_sync' => $church->privatbank_last_sync,

            'total_income' => (clone $baseQuery)->income()->sum('amount') / 100,
            'imported_income' => (clone $baseQuery)->where('is_processed', true)->sum('amount') / 100,

            'this_month_count' => (clone $baseQuery)->income()->where('privat_time', '>=', $thisMonth)->count(),
            'this_month_amount' => (clone $baseQuery)->income()->where('privat_time', '>=', $thisMonth)->sum('amount') / 100,

            'last_month_count' => (clone $baseQuery)->income()
                ->where('privat_time', '>=', $lastMonth)
                ->where('privat_time', '<', $thisMonth)
                ->count(),
            'last_month_amount' => (clone $baseQuery)->income()
                ->where('privat_time', '>=', $lastMonth)
                ->where('privat_time', '<', $thisMonth)
                ->sum('amount') / 100,
        ];
    }

    /**
     * Connect PrivatBank (save credentials)
     */
    public function connect(Request $request)
    {
        $request->validate([
            'merchant_id' => 'required|string|min:3',
            'password' => 'required|string|min:3',
            'card_number' => 'required|string|size:16',
        ]);

        $church = $this->getCurrentChurch();
        $service = new PrivatbankService();

        // Set credentials for validation
        $service->setCredentials($request->merchant_id, $request->password, $request->card_number);

        // Validate credentials
        $validation = $service->validateCredentials();

        if (!$validation) {
            return back()->withErrors(['merchant_id' => 'Невірні дані або помилка з\'єднання з ПриватБанком']);
        }

        // Save credentials
        $service->saveCredentials($church, $request->merchant_id, $request->password, $request->card_number);

        return redirect()->route('finances.privatbank.index')
            ->with('success', "ПриватБанк підключено! Картка: {$validation['card']}");
    }

    /**
     * Disconnect PrivatBank
     */
    public function disconnect()
    {
        $church = $this->getCurrentChurch();
        $service = new PrivatbankService();
        $service->disconnect($church);

        return redirect()->route('finances.privatbank.index')
            ->with('success', 'ПриватБанк відключено');
    }

    /**
     * Sync transactions
     */
    public function sync(Request $request)
    {
        $church = $this->getCurrentChurch();
        $service = new PrivatbankService($church);

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
    public function import(Request $request, PrivatbankTransaction $privatTransaction)
    {
        $church = $this->getCurrentChurch();

        if ($privatTransaction->church_id !== $church->id) {
            abort(403);
        }

        if ($privatTransaction->is_processed) {
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
            'direction' => Transaction::DIRECTION_IN,
            'source_type' => Transaction::SOURCE_DONATION,
            'amount' => $privatTransaction->amount_uah,
            'currency' => 'UAH',
            'category_id' => $request->category_id,
            'person_id' => $request->person_id,
            'description' => $request->description ?: $privatTransaction->counterpart_display,
            'date' => $privatTransaction->privat_time->toDateString(),
            'status' => Transaction::STATUS_COMPLETED,
            'payment_method' => Transaction::PAYMENT_CARD,
        ]);

        // Mark as processed
        $privatTransaction->update([
            'is_processed' => true,
            'transaction_id' => $transaction->id,
            'person_id' => $request->person_id,
        ]);

        return back()->with('success', 'Транзакцію імпортовано');
    }

    /**
     * Ignore transaction
     */
    public function ignore(PrivatbankTransaction $privatTransaction)
    {
        $church = $this->getCurrentChurch();

        if ($privatTransaction->church_id !== $church->id) {
            abort(403);
        }

        $privatTransaction->update(['is_ignored' => true]);

        return back()->with('success', 'Транзакцію приховано');
    }

    /**
     * Restore ignored transaction
     */
    public function restore(PrivatbankTransaction $privatTransaction)
    {
        $church = $this->getCurrentChurch();

        if ($privatTransaction->church_id !== $church->id) {
            abort(403);
        }

        $privatTransaction->update(['is_ignored' => false]);

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
            $privatTx = PrivatbankTransaction::where('id', $id)
                ->where('church_id', $church->id)
                ->where('is_processed', false)
                ->where('is_income', true)
                ->first();

            if (!$privatTx) continue;

            $transaction = Transaction::create([
                'church_id' => $church->id,
                'direction' => Transaction::DIRECTION_IN,
                'source_type' => Transaction::SOURCE_DONATION,
                'amount' => $privatTx->amount_uah,
                'currency' => 'UAH',
                'category_id' => $request->category_id,
                'description' => $privatTx->counterpart_display,
                'date' => $privatTx->privat_time->toDateString(),
                'status' => Transaction::STATUS_COMPLETED,
                'payment_method' => Transaction::PAYMENT_CARD,
            ]);

            $privatTx->update([
                'is_processed' => true,
                'transaction_id' => $transaction->id,
            ]);

            $imported++;
        }

        return back()->with('success', "Імпортовано {$imported} транзакцій");
    }

    /**
     * Bulk ignore selected transactions
     */
    public function bulkIgnore(Request $request)
    {
        $church = $this->getCurrentChurch();

        $request->validate([
            'transaction_ids' => 'required|array|min:1',
            'transaction_ids.*' => 'integer',
        ]);

        $count = PrivatbankTransaction::whereIn('id', $request->transaction_ids)
            ->where('church_id', $church->id)
            ->where('is_processed', false)
            ->update(['is_ignored' => true]);

        return back()->with('success', "Приховано {$count} транзакцій");
    }

    /**
     * Toggle auto-sync setting
     */
    public function toggleAutoSync(Request $request)
    {
        $church = $this->getCurrentChurch();
        $church->update(['privatbank_auto_sync' => !$church->privatbank_auto_sync]);

        $status = $church->privatbank_auto_sync ? 'увімкнено' : 'вимкнено';
        return back()->with('success', "Автосинхронізацію {$status}");
    }
}
