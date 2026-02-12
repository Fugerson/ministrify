<?php

namespace App\Http\Controllers;

use App\Models\MonobankTransaction;
use App\Models\MonobankSenderMapping;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\Person;
use App\Services\MonobankPersonalService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class MonobankSyncController extends Controller
{
    /**
     * Show Monobank integration page with filters
     */
    public function index(Request $request)
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

        // Build query with filters
        $query = MonobankTransaction::where('church_id', $church->id);

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
            $query->where('mono_time', '>=', Carbon::parse($request->date_from)->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->where('mono_time', '<=', Carbon::parse($request->date_to)->endOfDay());
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
            $search = addcslashes($request->search, '%_');
            $query->where(function ($q) use ($search) {
                $q->where('counterpart_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('comment', 'like', "%{$search}%")
                  ->orWhere('counterpart_iban', 'like', "%{$search}%");
            });
        }

        // Category filter (for imported transactions)
        if ($request->filled('category_id')) {
            $query->whereHas('transaction', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        // MCC category filter (for expenses)
        if ($request->filled('mcc_category')) {
            $query->mccCategory($request->mcc_category);
        }

        // Sorting
        $sortField = $request->get('sort', 'mono_time');
        $sortDir = $request->get('dir', 'desc');

        $allowedSorts = ['mono_time', 'amount', 'counterpart_name', 'mcc'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'mono_time';
        }
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

        $transactions = $query->orderBy($sortField, $sortDir)->paginate(50)->withQueryString();

        // Stats
        $stats = $this->getStats($church);

        // Get categories for filters and import
        $categories = TransactionCategory::where('church_id', $church->id)
            ->where('type', 'income')
            ->orderBy('name')
            ->get();

        // Default donation category
        $donationCategory = $categories->firstWhere('is_donation', true) ?? $categories->first();

        // Get people for import dropdown
        $people = Person::where('church_id', $church->id)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'iban']);

        // MCC categories for expenses filter
        $mccCategories = MonobankTransaction::getMccCategories();

        return view('finances.monobank.index', compact(
            'isConnected',
            'clientName',
            'accounts',
            'transactions',
            'stats',
            'church',
            'categories',
            'donationCategory',
            'people',
            'tab',
            'mccCategories',
            'sortField',
            'sortDir'
        ));
    }

    /**
     * Get statistics
     */
    protected function getStats($church): array
    {
        $baseQuery = MonobankTransaction::where('church_id', $church->id);

        // Current month stats
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            'total' => (clone $baseQuery)->count(),
            'income' => (clone $baseQuery)->income()->count(),
            'unprocessed' => (clone $baseQuery)->unprocessedIncome()->count(),
            'ignored' => (clone $baseQuery)->where('is_ignored', true)->count(),
            'last_sync' => $church->monobank_last_sync,

            // Amounts
            'total_income' => (clone $baseQuery)->income()->sum('amount') / 100,
            'imported_income' => (clone $baseQuery)->where('is_processed', true)->sum('amount') / 100,

            // This month
            'this_month_count' => (clone $baseQuery)->income()->where('mono_time', '>=', $thisMonth)->count(),
            'this_month_amount' => (clone $baseQuery)->income()->where('mono_time', '>=', $thisMonth)->sum('amount') / 100,

            // Last month
            'last_month_count' => (clone $baseQuery)->income()
                ->where('mono_time', '>=', $lastMonth)
                ->where('mono_time', '<', $thisMonth)
                ->count(),
            'last_month_amount' => (clone $baseQuery)->income()
                ->where('mono_time', '>=', $lastMonth)
                ->where('mono_time', '<', $thisMonth)
                ->sum('amount') / 100,
        ];
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

        // Log Monobank connection
        $this->logAuditAction('settings_updated', 'Church', $church->id, $church->name, [
            'action' => 'monobank_connected',
            'client_name' => $validation['name'],
        ]);

        return redirect()->route('finances.monobank.index')
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

        // Log Monobank disconnection
        $this->logAuditAction('settings_updated', 'Church', $church->id, $church->name, [
            'action' => 'monobank_disconnected',
        ]);

        return redirect()->route('finances.monobank.index')
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

        // Log sync action
        $this->logAuditAction('monobank_synced', 'Church', $church->id, $church->name, [
            'imported' => $result['imported'],
            'skipped' => $result['skipped'],
            'days' => $days,
        ]);

        $message = "Синхронізовано: {$result['imported']} нових транзакцій";
        if ($result['skipped'] > 0) {
            $message .= " ({$result['skipped']} вже існували)";
        }

        return back()->with('success', $message);
    }

    /**
     * Get suggestions for a transaction (person & category based on history)
     */
    public function getSuggestions(MonobankTransaction $monoTransaction)
    {
        $church = $this->getCurrentChurch();

        if ($monoTransaction->church_id !== $church->id) {
            abort(403);
        }

        // Find mapping by IBAN or name
        $mapping = MonobankSenderMapping::findForSender(
            $church->id,
            $monoTransaction->counterpart_iban,
            $monoTransaction->counterpart_name
        );

        // Try to find person by IBAN
        $personByIban = null;
        if ($monoTransaction->counterpart_iban) {
            $personByIban = Person::where('church_id', $church->id)
                ->where('iban', $monoTransaction->counterpart_iban)
                ->first();
        }

        // Count previous transactions from this sender
        $previousCount = MonobankTransaction::where('church_id', $church->id)
            ->where('id', '!=', $monoTransaction->id)
            ->where(function ($q) use ($monoTransaction) {
                if ($monoTransaction->counterpart_iban) {
                    $q->where('counterpart_iban', $monoTransaction->counterpart_iban);
                } else {
                    $q->where('counterpart_name', $monoTransaction->counterpart_name);
                }
            })
            ->count();

        return response()->json([
            'mapping' => $mapping,
            'person_by_iban' => $personByIban,
            'previous_transactions' => $previousCount,
            'suggested_person_id' => $personByIban?->id ?? $mapping?->person_id,
            'suggested_category_id' => $mapping?->category_id,
        ]);
    }

    /**
     * Import transaction as donation with smart mapping
     */
    public function import(Request $request, MonobankTransaction $monoTransaction)
    {
        $church = $this->getCurrentChurch();

        if ($monoTransaction->church_id !== $church->id) {
            abort(403);
        }

        $request->validate([
            'category_id' => ['required', Rule::exists('transaction_categories', 'id')->where('church_id', $church->id)],
            'person_id' => ['nullable', Rule::exists('people', 'id')->where('church_id', $church->id)],
            'description' => 'nullable|string|max:500',
            'save_iban' => 'nullable|boolean',
        ]);

        // Use DB transaction with lock to prevent duplicate imports
        $transaction = \Illuminate\Support\Facades\DB::transaction(function () use ($church, $monoTransaction, $request) {
            $monoTransaction = MonobankTransaction::where('id', $monoTransaction->id)
                ->lockForUpdate()
                ->first();

            if ($monoTransaction->is_processed) {
                return null;
            }

            $transaction = Transaction::create([
                'church_id' => $church->id,
                'direction' => Transaction::DIRECTION_IN,
                'source_type' => Transaction::SOURCE_DONATION,
                'amount' => $monoTransaction->amount_uah,
                'currency' => 'UAH',
                'category_id' => $request->category_id,
                'person_id' => $request->person_id,
                'description' => $request->description ?: $monoTransaction->counterpart_display,
                'date' => $monoTransaction->mono_time->toDateString(),
                'status' => Transaction::STATUS_COMPLETED,
                'payment_method' => Transaction::PAYMENT_CARD,
            ]);

            $monoTransaction->update([
                'is_processed' => true,
                'transaction_id' => $transaction->id,
                'person_id' => $request->person_id,
            ]);

            return $transaction;
        });

        if (!$transaction) {
            return back()->with('error', 'Транзакція вже оброблена');
        }

        // Update sender mapping for smart categorization
        MonobankSenderMapping::updateFromImport(
            $church->id,
            $monoTransaction->counterpart_iban,
            $monoTransaction->counterpart_name,
            $request->category_id,
            $request->person_id
        );

        // Save IBAN to person if requested
        if ($request->save_iban && $request->person_id && $monoTransaction->counterpart_iban) {
            Person::where('id', $request->person_id)->update([
                'iban' => $monoTransaction->counterpart_iban,
            ]);
        }

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
            'category_id' => ['required', Rule::exists('transaction_categories', 'id')->where('church_id', $church->id)],
        ]);

        $imported = 0;

        foreach ($request->transaction_ids as $id) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($id, $church, $request, &$imported) {
                $monoTx = MonobankTransaction::where('id', $id)
                    ->where('church_id', $church->id)
                    ->where('is_processed', false)
                    ->where('is_income', true)
                    ->lockForUpdate()
                    ->first();

                if (!$monoTx) return;

                // Try to find person by IBAN
                $personId = null;
                if ($monoTx->counterpart_iban) {
                    $person = Person::where('church_id', $church->id)
                        ->where('iban', $monoTx->counterpart_iban)
                        ->first();
                    $personId = $person?->id;
                }

                $transaction = Transaction::create([
                    'church_id' => $church->id,
                    'direction' => Transaction::DIRECTION_IN,
                    'source_type' => Transaction::SOURCE_DONATION,
                    'amount' => $monoTx->amount_uah,
                    'currency' => 'UAH',
                    'category_id' => $request->category_id,
                    'person_id' => $personId,
                    'description' => $monoTx->counterpart_display,
                    'date' => $monoTx->mono_time->toDateString(),
                    'status' => Transaction::STATUS_COMPLETED,
                    'payment_method' => Transaction::PAYMENT_CARD,
                ]);

                $monoTx->update([
                    'is_processed' => true,
                    'transaction_id' => $transaction->id,
                    'person_id' => $personId,
                ]);

                // Update mapping
                MonobankSenderMapping::updateFromImport(
                    $church->id,
                    $monoTx->counterpart_iban,
                    $monoTx->counterpart_name,
                    $request->category_id,
                    $personId
                );

                $imported++;
            });
        }

        // Log bulk import
        if ($imported > 0) {
            $this->logAuditAction('imported', 'Transaction', null, 'Масовий імпорт з Monobank', [
                'count' => $imported,
                'category_id' => $request->category_id,
            ]);
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

        $count = MonobankTransaction::whereIn('id', $request->transaction_ids)
            ->where('church_id', $church->id)
            ->where('is_processed', false)
            ->update(['is_ignored' => true]);

        return back()->with('success', "Приховано {$count} транзакцій");
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

        return response()->json($transactions->map(function ($tx) {
            $data = $tx->toArray();
            $data['masked_iban'] = $tx->masked_iban;
            return $data;
        }));
    }

    /**
     * Toggle auto-sync setting
     */
    public function toggleAutoSync(Request $request)
    {
        $church = $this->getCurrentChurch();
        $church->update(['monobank_auto_sync' => !$church->monobank_auto_sync]);

        $status = $church->monobank_auto_sync ? 'увімкнено' : 'вимкнено';
        return back()->with('success', "Автосинхронізацію {$status}");
    }

    /**
     * Webhook endpoint for real-time transactions
     */
    public function webhook(Request $request, string $secret)
    {
        // Find church by webhook secret
        $church = \App\Models\Church::where('monobank_webhook_secret', $secret)->first();

        if (!$church) {
            return response('Invalid secret', 404);
        }

        // Handle webhook data
        $data = $request->all();

        if (isset($data['type']) && $data['type'] === 'StatementItem' && isset($data['data'])) {
            $statementData = $data['data']['statementItem'] ?? null;
            if ($statementData) {
                MonobankTransaction::createFromMonoData($church->id, $statementData);
            }
        }

        return response('OK', 200);
    }

    /**
     * Setup webhook for real-time sync
     */
    public function setupWebhook(Request $request)
    {
        $church = $this->getCurrentChurch();
        $service = new MonobankPersonalService($church);

        // Generate webhook secret if not exists
        if (!$church->monobank_webhook_secret) {
            $church->update(['monobank_webhook_secret' => bin2hex(random_bytes(16))]);
        }

        $webhookUrl = route('monobank.webhook', ['secret' => $church->monobank_webhook_secret]);

        // Set webhook via Monobank API
        $result = $service->setWebhook($webhookUrl);

        if ($result) {
            return back()->with('success', 'Webhook налаштовано. Тепер транзакції будуть надходити автоматично.');
        }

        return back()->with('error', 'Не вдалося налаштувати webhook. Спробуйте пізніше.');
    }
}
