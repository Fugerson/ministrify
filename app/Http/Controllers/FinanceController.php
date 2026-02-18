<?php

namespace App\Http\Controllers;

use App\Helpers\CurrencyHelper;
use App\Models\AuditLog;
use App\Models\Church;
use App\Models\DonationCampaign;
use App\Models\ExchangeRate;
use App\Models\Ministry;
use App\Models\MinistryBudget;
use App\Models\Person;
use App\Models\Transaction;
use App\Models\TransactionAttachment;
use App\Models\TransactionCategory;
use App\Rules\BelongsToChurch;
use App\Services\ImageService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->canView('finances')) {
            return redirect()->route('dashboard')->with('error', 'У вас немає доступу до цього розділу.');
        }

        $church = $this->getCurrentChurch();

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        // Calculate totals using Transaction model
        $incomeQuery = Transaction::where('church_id', $church->id)->incoming()->completed();
        $expenseQuery = Transaction::where('church_id', $church->id)->outgoing()->completed();

        if ($month) {
            $incomeQuery->forMonth($year, $month);
            $expenseQuery->forMonth($year, $month);
            $periodLabel = $this->getMonthName($month) . ' ' . $year;
        } else {
            $incomeQuery->forYear($year);
            $expenseQuery->forYear($year);
            $periodLabel = $year . ' рік';
        }

        // Calculate totals in UAH (using amount_uah for converted amounts, fallback to amount)
        $totalIncome = (clone $incomeQuery)
            ->selectRaw('COALESCE(SUM(COALESCE(amount_uah, amount)), 0) as total')
            ->value('total') ?? 0;
        $totalExpense = (clone $expenseQuery)
            ->selectRaw('COALESCE(SUM(COALESCE(amount_uah, amount)), 0) as total')
            ->value('total') ?? 0;
        $periodBalance = $totalIncome - $totalExpense;

        // Overall balance (includes initial balance) - all in UAH
        $initialBalanceDate = $church->initial_balance_date;

        // Calculate initial balance total in UAH (including foreign currencies at current rate)
        $allInitialBalances = $church->getAllInitialBalances();
        $initialBalance = 0;
        foreach ($allInitialBalances as $currency => $amount) {
            if ($currency === 'UAH') {
                $initialBalance += $amount;
            } else {
                $initialBalance += ExchangeRate::toUah($amount, $currency);
            }
        }
        $allTimeIncome = Transaction::where('church_id', $church->id)->incoming()->completed()
            ->selectRaw('COALESCE(SUM(COALESCE(amount_uah, amount)), 0) as total')
            ->value('total') ?? 0;
        $allTimeExpense = Transaction::where('church_id', $church->id)->outgoing()->completed()
            ->selectRaw('COALESCE(SUM(COALESCE(amount_uah, amount)), 0) as total')
            ->value('total') ?? 0;
        $currentBalance = $initialBalance + $allTimeIncome - $allTimeExpense;

        // Calculate balances per currency (all time)
        $allTimeIncomeByCurrency = Transaction::where('church_id', $church->id)
            ->incoming()->completed()
            ->selectRaw('COALESCE(currency, "UAH") as currency, SUM(amount) as total')
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->toArray();

        $allTimeExpenseByCurrency = Transaction::where('church_id', $church->id)
            ->outgoing()->completed()
            ->selectRaw('COALESCE(currency, "UAH") as currency, SUM(amount) as total')
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->toArray();

        // Get initial balances per currency
        $initialBalances = $church->getAllInitialBalances();

        // Calculate balance per currency
        $balancesByCurrency = [];
        $allCurrencies = array_unique(array_merge(
            array_keys($allTimeIncomeByCurrency),
            array_keys($allTimeExpenseByCurrency),
            array_keys($initialBalances)
        ));
        foreach ($allCurrencies as $curr) {
            $initialBal = $initialBalances[$curr] ?? 0;
            $income = $allTimeIncomeByCurrency[$curr] ?? 0;
            $expense = $allTimeExpenseByCurrency[$curr] ?? 0;
            $balancesByCurrency[$curr] = $initialBal + $income - $expense;
        }

        // Get balances by currency for the period
        $incomeQueryForCurrency = Transaction::where('church_id', $church->id)->incoming()->completed();
        $expenseQueryForCurrency = Transaction::where('church_id', $church->id)->outgoing()->completed();

        if ($month) {
            $incomeQueryForCurrency->forMonth($year, $month);
            $expenseQueryForCurrency->forMonth($year, $month);
        } else {
            $incomeQueryForCurrency->forYear($year);
            $expenseQueryForCurrency->forYear($year);
        }

        $incomeByCurrency = $incomeQueryForCurrency->clone()
            ->selectRaw('currency, SUM(amount) as total')
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->toArray();

        $expenseByCurrency = $expenseQueryForCurrency->clone()
            ->selectRaw('currency, SUM(amount) as total')
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->toArray();

        // Get exchange rates
        $exchangeRates = ExchangeRate::getLatestRates();
        $enabledCurrencies = CurrencyHelper::getEnabledCurrencies($church->enabled_currencies);

        // Monthly data for chart
        $monthlyData = $this->getMonthlyData($church->id, $year);

        // Income by category - optimized single query with JOIN
        $incomeByCategoryRaw = TransactionCategory::where('transaction_categories.church_id', $church->id)
            ->forIncome()
            ->leftJoin('transactions', function ($join) use ($church, $year, $month) {
                $join->on('transaction_categories.id', '=', 'transactions.category_id')
                    ->where('transactions.church_id', $church->id)
                    ->where('transactions.direction', 'in')
                    ->where('transactions.status', 'completed');
                if ($month) {
                    $join->whereYear('transactions.date', $year)
                        ->whereMonth('transactions.date', $month);
                } else {
                    $join->whereYear('transactions.date', $year);
                }
            })
            ->selectRaw('transaction_categories.*, COALESCE(SUM(COALESCE(transactions.amount_uah, transactions.amount)), 0) as total_amount')
            ->groupBy('transaction_categories.id')
            ->orderByDesc('total_amount')
            ->get();

        $incomeByCategory = $incomeByCategoryRaw->filter(fn($c) => $c->total_amount > 0);

        // Expense by category - optimized single query with JOIN
        $expenseByCategoryRaw = TransactionCategory::where('transaction_categories.church_id', $church->id)
            ->forExpense()
            ->leftJoin('transactions', function ($join) use ($church, $year, $month) {
                $join->on('transaction_categories.id', '=', 'transactions.category_id')
                    ->where('transactions.church_id', $church->id)
                    ->where('transactions.direction', 'out')
                    ->where('transactions.status', 'completed');
                if ($month) {
                    $join->whereYear('transactions.date', $year)
                        ->whereMonth('transactions.date', $month);
                } else {
                    $join->whereYear('transactions.date', $year);
                }
            })
            ->selectRaw('transaction_categories.*, COALESCE(SUM(COALESCE(transactions.amount_uah, transactions.amount)), 0) as total_amount')
            ->groupBy('transaction_categories.id')
            ->orderByDesc('total_amount')
            ->get();

        $expenseByCategory = $expenseByCategoryRaw->filter(fn($c) => $c->total_amount > 0);

        // Expense by ministry - optimized single query with JOIN
        $expenseByMinistryRaw = Ministry::where('ministries.church_id', $church->id)
            ->leftJoin('transactions', function ($join) use ($church, $year, $month) {
                $join->on('ministries.id', '=', 'transactions.ministry_id')
                    ->where('transactions.church_id', $church->id)
                    ->where('transactions.direction', 'out')
                    ->where('transactions.status', 'completed');
                if ($month) {
                    $join->whereYear('transactions.date', $year)
                        ->whereMonth('transactions.date', $month);
                } else {
                    $join->whereYear('transactions.date', $year);
                }
            })
            ->selectRaw('ministries.*, COALESCE(SUM(COALESCE(transactions.amount_uah, transactions.amount)), 0) as total_expense')
            ->groupBy('ministries.id')
            ->orderByDesc('total_expense')
            ->get();

        $expenseByMinistry = $expenseByMinistryRaw->filter(fn($m) => $m->total_expense > 0);

        // Recent transactions
        $recentIncomes = Transaction::where('church_id', $church->id)
            ->incoming()
            ->completed()
            ->with(['category', 'person'])
            ->orderByDesc('date')
            ->limit(5)
            ->get();

        $recentExpenses = Transaction::where('church_id', $church->id)
            ->outgoing()
            ->completed()
            ->with(['category', 'ministry'])
            ->orderByDesc('date')
            ->limit(5)
            ->get();

        // Year comparison
        $yearComparison = $this->getYearComparison($church->id, $year);

        // === NEW: Quick Stats ===
        $quickStats = $this->getQuickStats($church->id);

        // === NEW: Active Campaigns ===
        $activeCampaigns = DonationCampaign::where('church_id', $church->id)
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        // === NEW: Payment Methods Breakdown ===
        $paymentMethodsQuery = Transaction::where('church_id', $church->id)
            ->incoming()
            ->completed();

        if ($month) {
            $paymentMethodsQuery->forMonth($year, $month);
        } else {
            $paymentMethodsQuery->forYear($year);
        }

        $paymentMethods = $paymentMethodsQuery
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get()
            ->map(fn($pm) => [
                'method' => $pm->payment_method,
                'label' => Transaction::PAYMENT_METHODS[$pm->payment_method] ?? $pm->payment_method ?? 'Інше',
                'count' => $pm->count,
                'total' => $pm->total,
            ]);

        // === NEW: Activity Feed (last 10 transactions) ===
        $activityFeed = Transaction::where('church_id', $church->id)
            ->completed()
            ->with(['category', 'person', 'ministry'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Categories and ministries for modal forms
        $incomeCategories = TransactionCategory::where('church_id', $church->id)
            ->forIncome()
            ->orderBy('sort_order')
            ->get();
        $expenseCategories = TransactionCategory::where('church_id', $church->id)
            ->forExpense()
            ->orderBy('sort_order')
            ->get();
        $ministries = Ministry::where('church_id', $church->id)->orderBy('name')->get();

        return view('finances.index', compact(
            'church', 'year', 'month', 'periodLabel',
            'totalIncome', 'totalExpense', 'periodBalance',
            'initialBalance', 'initialBalanceDate', 'currentBalance',
            'allTimeIncome', 'allTimeExpense',
            'incomeByCurrency', 'expenseByCurrency', 'balancesByCurrency',
            'exchangeRates', 'enabledCurrencies',
            'monthlyData',
            'incomeByCategory', 'expenseByCategory', 'expenseByMinistry',
            'recentIncomes', 'recentExpenses',
            'yearComparison',
            'quickStats', 'activeCampaigns', 'paymentMethods', 'activityFeed',
            'incomeCategories', 'expenseCategories', 'ministries'
        ));
    }

    /**
     * Financial Journal - comprehensive ledger view
     */
    public function journal(Request $request)
    {
        if (!auth()->user()->canView('finances')) {
            return redirect()->route('dashboard')->with('error', 'У вас немає доступу до цього розділу.');
        }

        $church = $this->getCurrentChurch();

        // Load ALL completed transactions for client-side period switching
        $transactions = Transaction::where('church_id', $church->id)
            ->completed()
            ->with(['category', 'person', 'ministry', 'recorder', 'attachments'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Balance before all transactions = church initial balance
        $balanceBeforeYear = (float) ($church->initial_balance ?? 0);

        // Get filter options
        $categories = TransactionCategory::where('church_id', $church->id)->orderBy('name')->get();
        $ministries = Ministry::where('church_id', $church->id)->orderBy('name')->get();
        $people = Person::where('church_id', $church->id)
            ->whereHas('transactions')
            ->orderBy('first_name')
            ->get();

        // Current balance (all time) - in UAH
        $currentBalance = (float) $church->initial_balance
            + (Transaction::where('church_id', $church->id)->incoming()->completed()
                ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')->value('total') ?? 0)
            - (Transaction::where('church_id', $church->id)->outgoing()->completed()
                ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')->value('total') ?? 0);

        // Initial period from request or default to month
        $initialPeriod = $request->get('period', 'month');

        // For modal forms
        $incomeCategories = TransactionCategory::where('church_id', $church->id)
            ->forIncome()
            ->orderBy('sort_order')
            ->get();
        $expenseCategories = TransactionCategory::where('church_id', $church->id)
            ->forExpense()
            ->orderBy('sort_order')
            ->get();
        $enabledCurrencies = CurrencyHelper::getEnabledCurrencies($church->enabled_currencies);
        $exchangeRates = ExchangeRate::getLatestRates();

        return view('finances.journal', compact(
            'transactions', 'initialPeriod', 'balanceBeforeYear', 'currentBalance',
            'categories', 'ministries', 'people',
            'incomeCategories', 'expenseCategories', 'enabledCurrencies', 'exchangeRates'
        ));
    }

    /**
     * Export journal to Excel
     */
    public function journalExport(Request $request)
    {
        if (!auth()->user()->canView('finances')) {
            abort(403, 'Немає доступу до фінансових даних');
        }

        $church = $this->getCurrentChurch();

        $period = $request->get('period', 'month');
        $dates = $this->getJournalDates($period, $request->get('start_date'), $request->get('end_date'));

        $query = Transaction::where('church_id', $church->id)
            ->completed()
            ->whereBetween('date', [$dates['start'], $dates['end']])
            ->with(['category', 'person', 'ministry']);

        if ($direction = $request->get('direction')) {
            $query->where('direction', $direction);
        }
        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }
        if ($ministryId = $request->get('ministry_id')) {
            $query->where('ministry_id', $ministryId);
        }

        $transactions = $query->orderBy('date', 'desc')->get();

        // Log export
        $this->logAuditAction('exported', 'Transaction', null, 'Експорт фінансового журналу', [
            'period' => $period,
            'start_date' => $dates['start']->format('Y-m-d'),
            'end_date' => $dates['end']->format('Y-m-d'),
            'count' => $transactions->count(),
        ]);

        $filename = 'journal_' . $dates['start']->format('Y-m-d') . '_' . $dates['end']->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            // BOM for UTF-8
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['Дата', 'Тип', 'Категорія', 'Команда', 'Опис', 'Особа', 'Сума', 'Валюта', 'Спосіб оплати'], ';');

            foreach ($transactions as $t) {
                fputcsv($file, [
                    $t->date->format('d.m.Y'),
                    $t->direction === 'in' ? 'Надходження' : 'Витрата',
                    $t->category?->name ?? '-',
                    $t->ministry?->name ?? '-',
                    $t->description,
                    $t->person ? $t->person->first_name . ' ' . $t->person->last_name : '-',
                    ($t->direction === 'in' ? '+' : '-') . number_format($t->amount, 2, ',', ''),
                    $t->currency ?? 'UAH',
                    Transaction::PAYMENT_METHODS[$t->payment_method] ?? $t->payment_method ?? '-',
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getJournalDates(string $period, ?string $startDate, ?string $endDate): array
    {
        $now = Carbon::now();

        switch ($period) {
            case 'today':
                return ['start' => $now->copy()->startOfDay(), 'end' => $now->copy()->endOfDay()];
            case 'week':
                return ['start' => $now->copy()->startOfWeek(), 'end' => $now->copy()->endOfWeek()];
            case 'month':
                return ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy()->endOfMonth()];
            case 'quarter':
                return ['start' => $now->copy()->startOfQuarter(), 'end' => $now->copy()->endOfQuarter()];
            case 'year':
                return ['start' => $now->copy()->startOfYear(), 'end' => $now->copy()->endOfYear()];
            case 'custom':
                return [
                    'start' => $startDate ? Carbon::parse($startDate)->startOfDay() : $now->copy()->startOfMonth(),
                    'end' => $endDate ? Carbon::parse($endDate)->endOfDay() : $now->copy()->endOfDay(),
                ];
            default:
                return ['start' => $now->copy()->startOfMonth(), 'end' => $now->copy()->endOfMonth()];
        }
    }

    private function calculateBalanceBeforeDate(int $churchId, $date): float
    {
        $church = Church::find($churchId);
        $initialBalance = (float) ($church->initial_balance ?? 0);

        $income = Transaction::where('church_id', $churchId)
            ->incoming()->completed()
            ->where('date', '<', $date)
            ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')
            ->value('total') ?? 0;

        $expense = Transaction::where('church_id', $churchId)
            ->outgoing()->completed()
            ->where('date', '<', $date)
            ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')
            ->value('total') ?? 0;

        return $initialBalance + $income - $expense;
    }

    // Income/Transactions list
    public function incomes(Request $request)
    {
        if (!auth()->user()->canView('finances')) {
            return redirect()->route('dashboard')->with('error', 'У вас немає доступу до цього розділу.');
        }

        $church = $this->getCurrentChurch();

        // Support both old year/month and new start_date/end_date params
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->get('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->get('end_date'))->endOfDay();
        } else {
            $year = $request->get('year', now()->year);
            $month = $request->get('month', now()->month);
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        }

        $incomes = Transaction::where('church_id', $church->id)
            ->incoming()
            ->completed()
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['category', 'person', 'recorder'])
            ->orderByDesc('date')
            ->get();

        $categories = TransactionCategory::where('church_id', $church->id)
            ->forIncome()
            ->orderBy('sort_order')
            ->get();

        $totals = [
            'total' => $incomes->sum('amount_uah'),
        ];

        $enabledCurrencies = CurrencyHelper::getEnabledCurrencies($church->enabled_currencies);
        $exchangeRates = ExchangeRate::getLatestRates();

        $incomesJson = $incomes->map(function ($income) {
            return [
                'id' => $income->id,
                'date' => $income->date->format('d.m'),
                'date_full' => $income->date->format('Y-m-d'),
                'category_id' => $income->category_id,
                'category_name' => $income->category?->name ?? 'Без категорії',
                'category_icon' => $income->category?->icon ?? '💰',
                'category_color' => $income->category?->color ?? '#3B82F6',
                'payment_method' => $income->payment_method ?? '',
                'payment_method_label' => $income->payment_method_label,
                'person_name' => $income->is_anonymous ? 'Анонімно' : ($income->person ? $income->person->first_name . ' ' . $income->person->last_name : ''),
                'is_anonymous' => $income->is_anonymous,
                'notes' => $income->notes ?? '',
                'amount' => $income->amount,
                'amount_formatted' => CurrencyHelper::format($income->amount, $income->currency ?? 'UAH'),
                'currency' => $income->currency ?? 'UAH',
                'amount_uah' => $income->amount_uah,
            ];
        });

        return view('finances.incomes.index', compact('incomes', 'incomesJson', 'categories', 'totals', 'enabledCurrencies', 'exchangeRates'));
    }

    public function createIncome()
    {
        if (!auth()->user()->canCreate('finances')) {
            return redirect()->route('finances.index')->with('error', 'У вас немає прав для створення записів.');
        }

        $church = $this->getCurrentChurch();
        $categories = TransactionCategory::where('church_id', $church->id)
            ->forIncome()
            ->orderBy('sort_order')
            ->get();
        $enabledCurrencies = CurrencyHelper::getEnabledCurrencies($church->enabled_currencies);
        $exchangeRates = ExchangeRate::getLatestRates();

        return view('finances.incomes.create', compact('categories', 'enabledCurrencies', 'exchangeRates'));
    }

    public function storeIncome(Request $request)
    {
        if (!auth()->user()->canCreate('finances')) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'У вас немає прав для створення записів.'], 403);
            }
            abort(403, 'У вас немає прав для створення записів.');
        }

        $validated = $request->validate([
            'category_id' => ['required', 'exists:transaction_categories,id', new BelongsToChurch(TransactionCategory::class, 'income')],
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|in:UAH,USD,EUR',
            'date' => 'required|date',
            'person_id' => ['nullable', 'exists:people,id', new BelongsToChurch(Person::class)],
            'description' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,card',
            'is_anonymous' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $church = $this->getCurrentChurch();

        // Determine source type based on category
        $category = TransactionCategory::find($validated['category_id']);
        $sourceType = 'income';
        if ($category->is_tithe) {
            $sourceType = Transaction::SOURCE_TITHE;
        } elseif ($category->is_offering) {
            $sourceType = Transaction::SOURCE_OFFERING;
        } elseif ($category->is_donation) {
            $sourceType = Transaction::SOURCE_DONATION;
        }

        $transaction = Transaction::create([
            'church_id' => $church->id,
            'direction' => Transaction::DIRECTION_IN,
            'source_type' => $sourceType,
            'amount' => $validated['amount'],
            'currency' => $validated['currency'] ?? 'UAH',
            'date' => $validated['date'],
            'category_id' => $validated['category_id'],
            'person_id' => $request->boolean('is_anonymous') ? null : ($validated['person_id'] ?? null),
            'is_anonymous' => $request->boolean('is_anonymous'),
            'payment_method' => $validated['payment_method'] ?? null,
            'description' => $validated['description'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => Transaction::STATUS_COMPLETED,
            'recorded_by' => auth()->id(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Надходження додано!',
                'transaction' => $transaction->load('category'),
                'redirect_url' => route('finances.incomes'),
            ]);
        }

        return redirect()->route('finances.incomes')
            ->with('success', 'Надходження додано.');
    }

    public function editIncome(Transaction $transaction)
    {
        if (!auth()->user()->canEdit('finances')) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'У вас немає прав для редагування записів.'], 403);
            }
            return redirect()->route('finances.incomes')->with('error', 'У вас немає прав для редагування записів.');
        }

        $this->authorizeChurch($transaction);

        $church = $this->getCurrentChurch();
        $categories = TransactionCategory::where('church_id', $church->id)
            ->forIncome()
            ->orderBy('sort_order')
            ->get();
        $enabledCurrencies = CurrencyHelper::getEnabledCurrencies($church->enabled_currencies);
        $exchangeRates = ExchangeRate::getLatestRates();
        $income = $transaction;

        if (request()->wantsJson()) {
            return response()->json([
                'transaction' => $transaction,
                'categories' => $categories,
                'enabledCurrencies' => $enabledCurrencies,
                'exchangeRates' => $exchangeRates,
            ]);
        }

        return view('finances.incomes.edit', compact('income', 'categories', 'enabledCurrencies', 'exchangeRates'));
    }

    public function updateIncome(Request $request, Transaction $transaction)
    {
        if (!auth()->user()->canEdit('finances')) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'У вас немає прав для редагування записів.'], 403);
            }
            abort(403, 'У вас немає прав для редагування записів.');
        }

        $this->authorizeChurch($transaction);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:transaction_categories,id', new BelongsToChurch(TransactionCategory::class, 'income')],
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|in:UAH,USD,EUR',
            'date' => 'required|date',
            'person_id' => ['nullable', 'exists:people,id', new BelongsToChurch(Person::class)],
            'description' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,card',
            'is_anonymous' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['is_anonymous'] = $request->boolean('is_anonymous');
        if ($validated['is_anonymous']) {
            $validated['person_id'] = null;
        }
        $validated['currency'] = $validated['currency'] ?? 'UAH';

        $transaction->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Надходження оновлено!',
                'transaction' => $transaction->fresh()->load('category'),
            ]);
        }

        return redirect()->route('finances.incomes')
            ->with('success', 'Надходження оновлено.');
    }

    public function destroyIncome(Request $request, Transaction $transaction)
    {
        if (!auth()->user()->canDelete('finances')) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'У вас немає прав для видалення записів.'], 403);
            }
            abort(403, 'У вас немає прав для видалення записів.');
        }

        $this->authorizeChurch($transaction);
        $transaction->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Надходження видалено!',
            ]);
        }

        return redirect()->route('finances.incomes')->with('success', 'Надходження видалено.');
    }

    // Expenses
    public function expenses(Request $request)
    {
        if (!auth()->user()->canView('finances')) {
            return redirect()->route('dashboard')->with('error', 'У вас немає доступу до цього розділу.');
        }

        $church = $this->getCurrentChurch();

        // Support both old year/month and new start_date/end_date params
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::parse($request->get('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->get('end_date'))->endOfDay();
        } else {
            $year = $request->get('year', now()->year);
            $month = $request->get('month', now()->month);
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();
        }

        $expenses = Transaction::where('church_id', $church->id)
            ->outgoing()
            ->completed()
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['category', 'ministry', 'recorder'])
            ->orderByDesc('date')
            ->get();

        $categories = TransactionCategory::where('church_id', $church->id)
            ->forExpense()
            ->orderBy('sort_order')
            ->get();

        $ministries = Ministry::where('church_id', $church->id)->orderBy('name')->get();

        $budget = $ministries->whereNotNull('monthly_budget')->sum('monthly_budget');

        $totals = [
            'budget' => $budget,
            'spent' => $expenses->sum('amount_uah'),
        ];

        $enabledCurrencies = CurrencyHelper::getEnabledCurrencies($church->enabled_currencies);
        $exchangeRates = ExchangeRate::getLatestRates();

        $expensesJson = $expenses->map(function ($expense) {
            return [
                'id' => $expense->id,
                'date' => $expense->date->format('d.m'),
                'date_full' => $expense->date->format('Y-m-d'),
                'description' => $expense->description ?? '',
                'notes' => $expense->notes ?? '',
                'ministry_id' => $expense->ministry_id,
                'ministry_name' => $expense->ministry?->name ?? '-',
                'category_id' => $expense->category_id,
                'category_name' => $expense->category?->name ?? '-',
                'payment_method' => $expense->payment_method ?? '',
                'payment_method_label' => $expense->payment_method_label,
                'expense_type' => $expense->expense_type ?? '',
                'amount' => $expense->amount,
                'amount_formatted' => CurrencyHelper::format($expense->amount, $expense->currency ?? 'UAH'),
                'currency' => $expense->currency ?? 'UAH',
                'amount_uah' => $expense->amount_uah,
            ];
        });

        return view('finances.expenses.index', compact('expenses', 'expensesJson', 'categories', 'totals', 'ministries', 'enabledCurrencies', 'exchangeRates'));
    }

    public function createExpense(Request $request)
    {
        if (!auth()->user()->canCreate('finances')) {
            return redirect()->route('finances.expenses.index')->with('error', 'У вас немає прав для створення записів.');
        }

        $church = $this->getCurrentChurch();
        $categories = TransactionCategory::where('church_id', $church->id)
            ->forExpense()
            ->orderBy('sort_order')
            ->get();
        $ministries = Ministry::where('church_id', $church->id)->orderBy('name')->get();
        $selectedMinistry = $request->get('ministry');
        $enabledCurrencies = CurrencyHelper::getEnabledCurrencies($church->enabled_currencies);
        $exchangeRates = ExchangeRate::getLatestRates();

        return view('finances.expenses.create', compact('categories', 'ministries', 'selectedMinistry', 'enabledCurrencies', 'exchangeRates'));
    }

    public function storeExpense(Request $request)
    {
        if (!auth()->user()->canCreate('finances')) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'У вас немає прав для створення записів.'], 403);
            }
            abort(403, 'У вас немає прав для створення записів.');
        }

        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:transaction_categories,id', new BelongsToChurch(TransactionCategory::class, 'expense')],
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|in:UAH,USD,EUR',
            'date' => 'required|date',
            'ministry_id' => ['nullable', 'exists:ministries,id', new BelongsToChurch(Ministry::class)],
            'description' => 'required|string|max:255',
            'payment_method' => 'nullable|in:cash,card',
            'expense_type' => 'nullable|in:recurring,one_time',
            'notes' => 'nullable|string',
            'force_over_budget' => 'boolean',
            'receipts' => 'nullable|array|max:10',
            'receipts.*' => 'file|mimes:jpg,jpeg,png,gif,webp,heic,heif,pdf|max:10240',
        ]);

        $church = $this->getCurrentChurch();

        // Check ministry budget limits
        $budgetWarning = null;
        if (!empty($validated['ministry_id'])) {
            $ministry = Ministry::find($validated['ministry_id']);
            if ($ministry) {
                $budgetCheck = $ministry->canAddExpense((float) $validated['amount']);

                if (!$budgetCheck['allowed'] && !$request->boolean('force_over_budget')) {
                    if ($request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => $budgetCheck['message'],
                            'budget_exceeded' => true,
                            'ministry_id' => $ministry->id,
                        ], 422);
                    }
                    return back()
                        ->with('error', $budgetCheck['message'])
                        ->with('budget_exceeded', true)
                        ->with('ministry_id', $ministry->id)
                        ->withInput();
                }

                if ($budgetCheck['warning']) {
                    $budgetWarning = $budgetCheck['message'];
                }
            }
        }

        $transaction = Transaction::create([
            'church_id' => $church->id,
            'direction' => Transaction::DIRECTION_OUT,
            'source_type' => Transaction::SOURCE_EXPENSE,
            'expense_type' => $validated['expense_type'] ?? null,
            'amount' => $validated['amount'],
            'currency' => $validated['currency'] ?? 'UAH',
            'date' => $validated['date'],
            'category_id' => $validated['category_id'] ?? null,
            'ministry_id' => $validated['ministry_id'] ?? null,
            'payment_method' => $validated['payment_method'] ?? null,
            'description' => $validated['description'],
            'notes' => $validated['notes'] ?? null,
            'status' => Transaction::STATUS_COMPLETED,
            'recorded_by' => auth()->id(),
        ]);

        // Handle receipt uploads

        if ($request->hasFile('receipts')) {
            foreach ($request->file('receipts') as $file) {
                $stored = ImageService::storeWithHeicConversion($file, "receipts/{$church->id}");

                $transaction->attachments()->create([
                    'filename' => $stored['filename'],
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $stored['path'],
                    'mime_type' => $stored['mime_type'],
                    'size' => $stored['size'],
                ]);
            }
        }

        $message = 'Витрату додано.';
        if ($budgetWarning) {
            $message .= ' ' . $budgetWarning;
        }

        if ($request->wantsJson()) {
            $redirectUrl = route('finances.expenses.index');
            if (!empty($validated['ministry_id']) && $request->input('redirect_to') === 'ministry') {
                $redirectUrl = route('ministries.show', ['ministry' => $validated['ministry_id'], 'tab' => 'expenses']);
            }
            return response()->json([
                'success' => true,
                'message' => $message,
                'transaction' => $transaction->load(['category', 'ministry']),
                'budget_warning' => $budgetWarning,
                'redirect_url' => $redirectUrl,
            ]);
        }

        // Redirect back to ministry page if came from there
        if (!empty($validated['ministry_id']) && $request->input('redirect_to') === 'ministry') {
            return redirect()->route('ministries.show', ['ministry' => $validated['ministry_id'], 'tab' => 'expenses'])
                ->with('success', $message)
                ->with('budget_warning', $budgetWarning);
        }

        return redirect()->route('finances.expenses.index')
            ->with('success', $message)
            ->with('budget_warning', $budgetWarning);
    }

    public function editExpense(Transaction $transaction)
    {
        if (!auth()->user()->canEdit('finances')) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'У вас немає прав для редагування записів.'], 403);
            }
            return redirect()->route('finances.expenses.index')->with('error', 'У вас немає прав для редагування записів.');
        }

        $this->authorizeChurch($transaction);

        $church = $this->getCurrentChurch();
        $categories = TransactionCategory::where('church_id', $church->id)
            ->forExpense()
            ->orderBy('sort_order')
            ->get();
        $ministries = Ministry::where('church_id', $church->id)->orderBy('name')->get();
        $enabledCurrencies = CurrencyHelper::getEnabledCurrencies($church->enabled_currencies);
        $exchangeRates = ExchangeRate::getLatestRates();

        $transaction->load('attachments');
        $expense = $transaction;

        if (request()->wantsJson()) {
            return response()->json([
                'transaction' => $transaction,
                'categories' => $categories,
                'ministries' => $ministries,
                'enabledCurrencies' => $enabledCurrencies,
                'exchangeRates' => $exchangeRates,
            ]);
        }

        return view('finances.expenses.edit', compact('expense', 'categories', 'ministries', 'enabledCurrencies', 'exchangeRates'));
    }

    public function updateExpense(Request $request, Transaction $transaction)
    {
        if (!auth()->user()->canEdit('finances')) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'У вас немає прав для редагування записів.'], 403);
            }
            abort(403, 'У вас немає прав для редагування записів.');
        }

        $this->authorizeChurch($transaction);

        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:transaction_categories,id', new BelongsToChurch(TransactionCategory::class, 'expense')],
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|in:UAH,USD,EUR',
            'date' => 'required|date',
            'ministry_id' => ['nullable', 'exists:ministries,id', new BelongsToChurch(Ministry::class)],
            'description' => 'required|string|max:255',
            'payment_method' => 'nullable|in:cash,card',
            'expense_type' => 'nullable|in:recurring,one_time',
            'notes' => 'nullable|string',
            'force_over_budget' => 'boolean',
            'receipts' => 'nullable|array|max:10',
            'receipts.*' => 'file|mimes:jpg,jpeg,png,gif,webp,heic,heif,pdf|max:10240',
            'delete_attachments' => 'nullable|array',
            'delete_attachments.*' => 'integer|exists:transaction_attachments,id',
        ]);

        $validated['currency'] = $validated['currency'] ?? 'UAH';

        // Check ministry budget limits (only if amount increased or ministry changed)
        $budgetWarning = null;
        $newMinistryId = $validated['ministry_id'] ?? null;
        $amountDifference = (float) $validated['amount'] - (float) $transaction->amount;

        // Check budget if: ministry changed to new one, or amount increased for same ministry
        if ($newMinistryId) {
            $ministry = Ministry::find($newMinistryId);
            if ($ministry) {
                // Calculate effective new expense for budget check
                $checkAmount = ($transaction->ministry_id === $newMinistryId)
                    ? $amountDifference  // Same ministry - only check the increase
                    : (float) $validated['amount'];  // New ministry - check full amount

                if ($checkAmount > 0) {
                    $budgetCheck = $ministry->canAddExpense($checkAmount);

                    if (!$budgetCheck['allowed'] && !$request->boolean('force_over_budget')) {
                        if ($request->wantsJson()) {
                            return response()->json([
                                'success' => false,
                                'message' => $budgetCheck['message'],
                                'budget_exceeded' => true,
                            ], 422);
                        }
                        return back()
                            ->with('error', $budgetCheck['message'])
                            ->with('budget_exceeded', true)
                            ->withInput();
                    }

                    if ($budgetCheck['warning']) {
                        $budgetWarning = $budgetCheck['message'];
                    }
                }
            }
        }

        $transaction->update($validated);

        // Delete marked attachments
        if (!empty($validated['delete_attachments'])) {
            $transaction->attachments()
                ->whereIn('id', $validated['delete_attachments'])
                ->get()
                ->each(fn ($att) => $att->delete());
        }

        // Handle new receipt uploads
        $church = $this->getCurrentChurch();

        if ($request->hasFile('receipts')) {
            foreach ($request->file('receipts') as $file) {
                $stored = ImageService::storeWithHeicConversion($file, "receipts/{$church->id}");

                $transaction->attachments()->create([
                    'filename' => $stored['filename'],
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $stored['path'],
                    'mime_type' => $stored['mime_type'],
                    'size' => $stored['size'],
                ]);
            }
        }

        $message = 'Витрату оновлено.';
        if ($budgetWarning) {
            $message .= ' ' . $budgetWarning;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'transaction' => $transaction->fresh()->load(['category', 'ministry', 'attachments']),
                'budget_warning' => $budgetWarning,
            ]);
        }

        // Redirect back to ministry page if requested
        if ($request->input('redirect_to') === 'ministry' && $request->input('redirect_ministry_id')) {
            return redirect()->route('ministries.show', ['ministry' => $request->input('redirect_ministry_id'), 'tab' => 'expenses'])
                ->with('success', $message);
        }

        return redirect()->route('finances.expenses.index')
            ->with('success', $message);
    }

    public function destroyExpense(Request $request, Transaction $transaction)
    {
        if (!auth()->user()->canDelete('finances')) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'У вас немає прав для видалення записів.'], 403);
            }
            abort(403, 'У вас немає прав для видалення записів.');
        }

        $this->authorizeChurch($transaction);
        $ministryId = $transaction->ministry_id;
        $transaction->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Витрату видалено!',
            ]);
        }

        // Redirect back to ministry page if requested
        if ($request->input('redirect_to') === 'ministry' && $ministryId) {
            return redirect()->route('ministries.show', ['ministry' => $ministryId, 'tab' => 'expenses'])
                ->with('success', 'Витрату видалено.');
        }

        return back()->with('success', 'Витрату видалено.');
    }

    // Currency Exchange
    public function createExchange()
    {
        if (!auth()->user()->canCreate('finances')) {
            return redirect()->route('finances.index')->with('error', 'У вас немає прав для створення записів.');
        }

        $church = $this->getCurrentChurch();
        $enabledCurrencies = CurrencyHelper::getEnabledCurrencies($church->enabled_currencies);
        $exchangeRates = ExchangeRate::getLatestRates();

        return view('finances.exchange.create', compact('enabledCurrencies', 'exchangeRates'));
    }

    public function storeExchange(Request $request)
    {
        if (!auth()->user()->canCreate('finances')) {
            abort(403, 'У вас немає прав для створення записів.');
        }

        $validated = $request->validate([
            'from_currency' => 'required|in:UAH,USD,EUR',
            'to_currency' => 'required|in:UAH,USD,EUR|different:from_currency',
            'from_amount' => 'required|numeric|min:0.01',
            'to_amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $church = $this->getCurrentChurch();

        // Use database transaction to ensure both records are created atomically
        DB::transaction(function () use ($validated, $church) {
            // Create the outgoing transaction (from currency)
            $outTransaction = Transaction::create([
                'church_id' => $church->id,
                'direction' => Transaction::DIRECTION_OUT,
                'source_type' => Transaction::SOURCE_EXCHANGE,
                'amount' => $validated['from_amount'],
                'currency' => $validated['from_currency'],
                'date' => $validated['date'],
                'status' => Transaction::STATUS_COMPLETED,
                'payment_method' => Transaction::PAYMENT_CASH,
                'description' => "Обмін {$validated['from_currency']} → {$validated['to_currency']}",
                'notes' => $validated['notes'],
            ]);

            // Create the incoming transaction (to currency)
            $inTransaction = Transaction::create([
                'church_id' => $church->id,
                'direction' => Transaction::DIRECTION_IN,
                'source_type' => Transaction::SOURCE_EXCHANGE,
                'amount' => $validated['to_amount'],
                'currency' => $validated['to_currency'],
                'date' => $validated['date'],
                'status' => Transaction::STATUS_COMPLETED,
                'payment_method' => Transaction::PAYMENT_CASH,
                'description' => "Обмін {$validated['from_currency']} → {$validated['to_currency']}",
                'notes' => $validated['notes'],
                'related_transaction_id' => $outTransaction->id,
            ]);

            // Link back
            $outTransaction->update(['related_transaction_id' => $inTransaction->id]);
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Обмін валюти зареєстровано!',
            ]);
        }

        return redirect()->route('finances.index')->with('success', 'Обмін валюти зареєстровано.');
    }

    // Categories (unified)
    public function categories()
    {
        return redirect()->route('finances.index');
    }

    public function storeCategory(Request $request)
    {
        if (!auth()->user()->canCreate('finances')) {
            abort(403, 'У вас немає прав для створення категорій.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'is_tithe' => 'boolean',
            'is_offering' => 'boolean',
            'is_donation' => 'boolean',
        ]);

        $church = $this->getCurrentChurch();

        $validated['church_id'] = $church->id;
        $validated['sort_order'] = TransactionCategory::where('church_id', $church->id)
            ->where('type', $validated['type'])
            ->max('sort_order') + 1;

        TransactionCategory::create($validated);

        return back()->with('success', 'Категорію додано.');
    }

    public function updateCategory(Request $request, TransactionCategory $category)
    {
        if (!auth()->user()->canEdit('finances')) {
            abort(403, 'У вас немає прав для редагування категорій.');
        }

        if ($category->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'is_tithe' => 'boolean',
            'is_offering' => 'boolean',
            'is_donation' => 'boolean',
        ]);

        $category->update($validated);

        return back()->with('success', 'Категорію оновлено.');
    }

    public function destroyCategory(TransactionCategory $category)
    {
        if (!auth()->user()->canDelete('finances')) {
            abort(403, 'У вас немає прав для видалення категорій.');
        }

        if ($category->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }

        if ($category->transactions()->count() > 0) {
            return back()->with('error', 'Неможливо видалити категорію з транзакціями.');
        }

        $category->delete();

        return back()->with('success', 'Категорію видалено.');
    }

    // Team Budgets
    public function budgets(Request $request)
    {
        if (!auth()->user()->canView('finances')) {
            return redirect()->route('dashboard')->with('error', 'У вас немає доступу до цього розділу.');
        }

        $church = $this->getCurrentChurch();
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $ministries = Ministry::where('church_id', $church->id)
            ->with(['budgets' => function ($q) use ($year, $month) {
                $q->where('year', $year)->where('month', $month);
            }])
            ->orderBy('name')
            ->get()
            ->map(function ($ministry) use ($year, $month) {
                $budget = $ministry->budgets->first();
                $spent = Transaction::where('ministry_id', $ministry->id)
                    ->where('direction', Transaction::DIRECTION_OUT)
                    ->forMonth($year, $month)
                    ->completed()
                    ->sum('amount');

                return [
                    'ministry' => $ministry,
                    'budget' => $budget,
                    'monthly_budget' => $budget?->monthly_budget ?? $ministry->monthly_budget ?? 0,
                    'spent' => $spent,
                    'remaining' => ($budget?->monthly_budget ?? $ministry->monthly_budget ?? 0) - $spent,
                    'percentage' => ($budget?->monthly_budget ?? $ministry->monthly_budget ?? 0) > 0
                        ? round(($spent / ($budget?->monthly_budget ?? $ministry->monthly_budget)) * 100, 1)
                        : 0,
                ];
            });

        $totals = [
            'budget' => $ministries->sum('monthly_budget'),
            'spent' => $ministries->sum('spent'),
            'remaining' => $ministries->sum('remaining'),
        ];

        // Get recent expenses without receipts (categories with receipt_required)
        $expensesMissingReceipts = Transaction::where('church_id', $church->id)
            ->where('direction', Transaction::DIRECTION_OUT)
            ->whereHas('category', fn($q) => $q->where('receipt_required', true))
            ->whereDoesntHave('attachments')
            ->forMonth($year, $month)
            ->with(['ministry', 'category'])
            ->orderByDesc('date')
            ->limit(10)
            ->get();

        // For expense edit modal
        $expenseCategories = TransactionCategory::where('church_id', $church->id)
            ->forExpense()
            ->orderBy('sort_order')
            ->get();
        $enabledCurrencies = CurrencyHelper::getEnabledCurrencies($church->enabled_currencies);
        $exchangeRates = ExchangeRate::getLatestRates();

        return view('finances.budgets.index', compact(
            'ministries',
            'totals',
            'expensesMissingReceipts',
            'year',
            'month',
            'expenseCategories',
            'enabledCurrencies',
            'exchangeRates'
        ));
    }

    public function updateBudget(Request $request, Ministry $ministry)
    {
        if (!auth()->user()->canEdit('finances')) {
            abort(403, 'У вас немає прав для редагування бюджетів.');
        }

        $church = $this->getCurrentChurch();

        if ($ministry->church_id !== $church->id) {
            abort(404);
        }

        $validated = $request->validate([
            'monthly_budget' => 'required|numeric|min:0',
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'notes' => 'nullable|string|max:500',
        ]);

        $existingBudget = MinistryBudget::where('church_id', $church->id)
            ->where('ministry_id', $ministry->id)
            ->where('year', $validated['year'])
            ->where('month', $validated['month'])
            ->first();

        $oldBudget = $existingBudget?->monthly_budget;

        MinistryBudget::updateOrCreate(
            [
                'church_id' => $church->id,
                'ministry_id' => $ministry->id,
                'year' => $validated['year'],
                'month' => $validated['month'],
            ],
            [
                'monthly_budget' => $validated['monthly_budget'],
                'notes' => $validated['notes'] ?? null,
            ]
        );

        // Log budget update
        $this->logAuditAction('budget_updated', 'Ministry', $ministry->id, $ministry->name, [
            'monthly_budget' => $validated['monthly_budget'],
            'year' => $validated['year'],
            'month' => $validated['month'],
        ], [
            'monthly_budget' => $oldBudget,
        ]);

        return back()->with('success', "Бюджет для \"{$ministry->name}\" оновлено.");
    }

    // Analytics API
    public function chartData(Request $request)
    {
        if (!auth()->user()->canView('finances')) {
            abort(403, 'Немає доступу до фінансових даних');
        }

        $church = $this->getCurrentChurch();
        $year = $request->get('year', now()->year);

        return response()->json($this->getMonthlyData($church->id, $year));
    }

    // Private helpers
    private function getMonthlyData(int $churchId, int $year): array
    {
        // Optimized: single query with groupBy instead of 24 queries
        $monthlyRaw = Transaction::where('church_id', $churchId)
            ->completed()
            ->whereYear('date', $year)
            ->selectRaw('MONTH(date) as month, direction, SUM(amount) as total')
            ->groupBy('month', 'direction')
            ->get();

        $grouped = $monthlyRaw->groupBy('month');

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthData = $grouped[$m] ?? collect();
            $income = (float) $monthData->where('direction', 'in')->sum('total');
            $expense = (float) $monthData->where('direction', 'out')->sum('total');

            $months[] = [
                'month' => $this->getMonthName($m),
                'income' => $income,
                'expense' => $expense,
                'balance' => $income - $expense,
            ];
        }
        return $months;
    }

    private function getYearComparison(int $churchId, int $year): array
    {
        $currentYear = [
            'income' => Transaction::where('church_id', $churchId)->incoming()->completed()->forYear($year)->sum('amount'),
            'expense' => Transaction::where('church_id', $churchId)->outgoing()->completed()->forYear($year)->sum('amount'),
        ];
        $currentYear['balance'] = $currentYear['income'] - $currentYear['expense'];

        $prevYear = [
            'income' => Transaction::where('church_id', $churchId)->incoming()->completed()->forYear($year - 1)->sum('amount'),
            'expense' => Transaction::where('church_id', $churchId)->outgoing()->completed()->forYear($year - 1)->sum('amount'),
        ];
        $prevYear['balance'] = $prevYear['income'] - $prevYear['expense'];

        $growth = [
            'income' => $prevYear['income'] > 0 ? round((($currentYear['income'] - $prevYear['income']) / $prevYear['income']) * 100, 1) : 0,
            'expense' => $prevYear['expense'] > 0 ? round((($currentYear['expense'] - $prevYear['expense']) / $prevYear['expense']) * 100, 1) : 0,
        ];

        return compact('currentYear', 'prevYear', 'growth');
    }

    private function getMonthName(int $month): string
    {
        $months = [
            1 => 'Січ', 2 => 'Лют', 3 => 'Бер', 4 => 'Кві',
            5 => 'Тра', 6 => 'Чер', 7 => 'Лип', 8 => 'Сер',
            9 => 'Вер', 10 => 'Жов', 11 => 'Лис', 12 => 'Гру',
        ];
        return $months[$month] ?? '';
    }

    /**
     * Get quick stats with optimized queries (reduced from 10+ to 2 queries)
     */
    private function getQuickStats(int $churchId): array
    {
        $now = Carbon::now();
        $thisWeekStart = $now->copy()->startOfWeek();
        $lastWeekStart = $now->copy()->subWeek()->startOfWeek();
        $lastWeekEnd = $now->copy()->subWeek()->endOfWeek();
        $thisMonthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // Single optimized query for all income stats
        $stats = DB::table('transactions')
            ->where('church_id', $churchId)
            ->where('direction', Transaction::DIRECTION_IN)
            ->where('status', Transaction::STATUS_COMPLETED)
            ->selectRaw("
                SUM(CASE WHEN date >= ? THEN amount ELSE 0 END) as this_week_income,
                SUM(CASE WHEN date >= ? AND date <= ? THEN amount ELSE 0 END) as last_week_income,
                SUM(CASE WHEN date >= ? THEN amount ELSE 0 END) as this_month_total,
                COUNT(CASE WHEN date >= ? THEN 1 END) as this_month_count,
                SUM(CASE WHEN date >= ? AND date <= ? THEN amount ELSE 0 END) as last_month_total,
                COUNT(CASE WHEN date >= ? AND date <= ? THEN 1 END) as last_month_count,
                COUNT(DISTINCT CASE WHEN date >= ? AND person_id IS NOT NULL THEN person_id END) as this_month_donors,
                COUNT(DISTINCT CASE WHEN date >= ? AND date <= ? AND person_id IS NOT NULL THEN person_id END) as last_month_donors
            ", [
                $thisWeekStart, // this_week_income
                $lastWeekStart, $lastWeekEnd, // last_week_income
                $thisMonthStart, // this_month_total
                $thisMonthStart, // this_month_count
                $lastMonthStart, $lastMonthEnd, // last_month_total
                $lastMonthStart, $lastMonthEnd, // last_month_count
                $thisMonthStart, // this_month_donors
                $lastMonthStart, $lastMonthEnd, // last_month_donors
            ])
            ->first();

        // Total transactions this month (includes both income and expense)
        $totalTransactions = DB::table('transactions')
            ->where('church_id', $churchId)
            ->where('status', Transaction::STATUS_COMPLETED)
            ->where('date', '>=', $thisMonthStart)
            ->count();

        // Calculate derived values
        $thisWeekIncome = (float) ($stats->this_week_income ?? 0);
        $lastWeekIncome = (float) ($stats->last_week_income ?? 0);
        $thisMonthTotal = (float) ($stats->this_month_total ?? 0);
        $thisMonthCount = (int) ($stats->this_month_count ?? 0);
        $lastMonthTotal = (float) ($stats->last_month_total ?? 0);
        $lastMonthCount = (int) ($stats->last_month_count ?? 0);
        $thisMonthDonors = (int) ($stats->this_month_donors ?? 0);
        $lastMonthDonors = (int) ($stats->last_month_donors ?? 0);

        $weekChange = $lastWeekIncome > 0
            ? round((($thisWeekIncome - $lastWeekIncome) / $lastWeekIncome) * 100, 0)
            : ($thisWeekIncome > 0 ? 100 : 0);

        $avgDonation = $thisMonthCount > 0 ? round($thisMonthTotal / $thisMonthCount, 0) : 0;
        $lastMonthAvg = $lastMonthCount > 0 ? round($lastMonthTotal / $lastMonthCount, 0) : 0;

        $avgChange = $lastMonthAvg > 0
            ? round((($avgDonation - $lastMonthAvg) / $lastMonthAvg) * 100, 0)
            : ($avgDonation > 0 ? 100 : 0);

        return [
            'thisWeekIncome' => $thisWeekIncome,
            'lastWeekIncome' => $lastWeekIncome,
            'weekChange' => $weekChange,
            'thisMonthDonors' => $thisMonthDonors,
            'lastMonthDonors' => $lastMonthDonors,
            'donorsChange' => $thisMonthDonors - $lastMonthDonors,
            'avgDonation' => $avgDonation,
            'avgChange' => $avgChange,
            'totalTransactions' => $totalTransactions,
        ];
    }

    protected function authorizeChurch($model): void
    {
        if ($model->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }

    /**
     * Unified bank cards page with Monobank and PrivatBank tabs
     */
    public function cards()
    {
        if (!auth()->user()->canView('finances')) {
            return redirect()->route('dashboard')->with('error', 'У вас немає доступу до цього розділу.');
        }

        $church = $this->getCurrentChurch();

        // Check connection status for both banks
        $monobankConnected = !empty($church->monobank_token);
        $privatbankConnected = !empty($church->privatbank_merchant_id);

        return view('finances.cards.index', compact('church', 'monobankConnected', 'privatbankConnected'));
    }
}
