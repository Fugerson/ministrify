<?php

namespace App\Http\Controllers;

use App\Events\ChurchDataUpdated;
use App\Events\TransactionCreated;
use App\Helpers\CurrencyHelper;
use App\Models\BudgetItem;
use App\Models\Church;
use App\Models\ChurchBudget;
use App\Models\ChurchBudgetItem;
use App\Models\DonationCampaign;
use App\Models\ExchangeRate;
use App\Models\Ministry;
use App\Models\MinistryBudget;
use App\Models\Person;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Rules\BelongsToChurch;
use App\Services\ImageService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $this->checkPlanFeature('finances');

        if (! auth()->user()->canView('finances')) {
            return $this->errorResponse($request, __('messages.no_access_to_section'));
        }

        $church = $this->getCurrentChurch();

        $year = max(2000, min(2100, (int) $request->get('year', now()->year)));
        $month = $request->has('year')
            ? ($request->filled('month') ? max(1, min(12, (int) $request->get('month'))) : 0)
            : (int) now()->month;

        // Calculate totals using Transaction model (exclude exchange/allocation to avoid double-counting)
        $excludeTypes = [Transaction::SOURCE_EXCHANGE, Transaction::SOURCE_ALLOCATION];
        $incomeQuery = Transaction::where('church_id', $church->id)->incoming()->completed()
            ->whereNotIn('source_type', $excludeTypes);
        $expenseQuery = Transaction::where('church_id', $church->id)->outgoing()->completed()
            ->whereNotIn('source_type', $excludeTypes);

        if ($month) {
            $incomeQuery->forMonth($year, $month);
            $expenseQuery->forMonth($year, $month);
            $periodLabel = $this->getMonthName($month).' '.$year;
        } else {
            $incomeQuery->forYear($year);
            $expenseQuery->forYear($year);
            $periodLabel = __('messages.year_label', ['year' => $year]);
        }

        // Calculate totals in UAH (using amount_uah for converted amounts, fallback to amount)
        $totalIncome = (clone $incomeQuery)
            ->selectRaw('COALESCE(SUM(COALESCE(amount_uah, amount)), 0) as total')
            ->value('total') ?? 0;
        $totalExpense = (clone $expenseQuery)
            ->selectRaw('COALESCE(SUM(COALESCE(amount_uah, amount)), 0) as total')
            ->value('total') ?? 0;
        $periodBalance = $totalIncome - $totalExpense;

        // Overall balance (includes initial balance) - all in UAH (cached 5 min)
        $allTimeTotals = Cache::remember('finance_alltime_' . $church->id, 300, function () use ($church, $excludeTypes) {
            $initialBalanceDate = $church->initial_balance_date;
            $allInitialBalances = $church->getAllInitialBalances();
            $initialBalance = 0;
            foreach ($allInitialBalances as $currency => $amount) {
                if ($currency === 'UAH') {
                    $initialBalance += $amount;
                } else {
                    $initialBalance += ExchangeRate::toUah($amount, $currency, $initialBalanceDate);
                }
            }
            $allTimeIncome = Transaction::where('church_id', $church->id)->incoming()->completed()
                ->whereNotIn('source_type', $excludeTypes)
                ->selectRaw('COALESCE(SUM(COALESCE(amount_uah, amount)), 0) as total')
                ->value('total') ?? 0;
            $allTimeExpense = Transaction::where('church_id', $church->id)->outgoing()->completed()
                ->whereNotIn('source_type', $excludeTypes)
                ->selectRaw('COALESCE(SUM(COALESCE(amount_uah, amount)), 0) as total')
                ->value('total') ?? 0;

            // Calculate "committed to teams" — unspent allocation balances per ministry
            $allocationsByMinistryAll = Transaction::where('church_id', $church->id)
                ->where('direction', Transaction::DIRECTION_IN)
                ->where('source_type', Transaction::SOURCE_ALLOCATION)
                ->completed()
                ->whereNotNull('ministry_id')
                ->selectRaw('ministry_id, SUM(COALESCE(amount_uah, amount)) as total')
                ->groupBy('ministry_id')
                ->pluck('total', 'ministry_id');

            $spentByMinistryAll = Transaction::where('church_id', $church->id)
                ->where('direction', Transaction::DIRECTION_OUT)
                ->whereNotIn('source_type', $excludeTypes)
                ->completed()
                ->whereNotNull('ministry_id')
                ->selectRaw('ministry_id, SUM(COALESCE(amount_uah, amount)) as total')
                ->groupBy('ministry_id')
                ->pluck('total', 'ministry_id');

            $committedToTeams = 0;
            foreach ($allocationsByMinistryAll as $ministryId => $allocated) {
                $spent = $spentByMinistryAll[$ministryId] ?? 0;
                $unspent = max(0, (float) $allocated - (float) $spent);
                $committedToTeams += $unspent;
            }

            return [
                'initialBalance' => $initialBalance,
                'initialBalanceDate' => $initialBalanceDate,
                'allTimeIncome' => $allTimeIncome,
                'allTimeExpense' => $allTimeExpense,
                'committedToTeams' => $committedToTeams,
            ];
        });

        $initialBalance = $allTimeTotals['initialBalance'];
        $initialBalanceDate = $allTimeTotals['initialBalanceDate'];
        $allTimeIncome = $allTimeTotals['allTimeIncome'];
        $allTimeExpense = $allTimeTotals['allTimeExpense'];
        $committedToTeams = $allTimeTotals['committedToTeams'];
        $currentBalance = $initialBalance + $allTimeIncome - $allTimeExpense;
        $availableBalance = $currentBalance - $committedToTeams;

        // Calculate balances per currency (all time, cached 5 min)
        $currencyExcludeTypes = [Transaction::SOURCE_ALLOCATION];
        $currencyTotals = Cache::remember('finance_currency_' . $church->id, 300, function () use ($church, $currencyExcludeTypes) {
            $allTimeIncomeByCurrency = Transaction::where('church_id', $church->id)
                ->incoming()->completed()
                ->whereNotIn('source_type', $currencyExcludeTypes)
                ->selectRaw('COALESCE(currency, "UAH") as currency, SUM(amount) as total')
                ->groupBy('currency')
                ->pluck('total', 'currency')
                ->toArray();

            $allTimeExpenseByCurrency = Transaction::where('church_id', $church->id)
                ->outgoing()->completed()
                ->whereNotIn('source_type', $currencyExcludeTypes)
                ->selectRaw('COALESCE(currency, "UAH") as currency, SUM(amount) as total')
                ->groupBy('currency')
                ->pluck('total', 'currency')
                ->toArray();

            $initialBalances = $church->getAllInitialBalances();

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

            return [
                'allTimeIncomeByCurrency' => $allTimeIncomeByCurrency,
                'allTimeExpenseByCurrency' => $allTimeExpenseByCurrency,
                'initialBalances' => $initialBalances,
                'balancesByCurrency' => $balancesByCurrency,
            ];
        });

        $allTimeIncomeByCurrency = $currencyTotals['allTimeIncomeByCurrency'];
        $allTimeExpenseByCurrency = $currencyTotals['allTimeExpenseByCurrency'];
        $initialBalances = $currencyTotals['initialBalances'];
        $balancesByCurrency = $currencyTotals['balancesByCurrency'];

        // Get balances by currency for the period (include exchange for proper currency tracking)
        $incomeQueryForCurrency = Transaction::where('church_id', $church->id)->incoming()->completed()
            ->whereNotIn('source_type', $currencyExcludeTypes);
        $expenseQueryForCurrency = Transaction::where('church_id', $church->id)->outgoing()->completed()
            ->whereNotIn('source_type', $currencyExcludeTypes);

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

        // Monthly data for chart (formatted for Chart.js: {labels, income, expense})
        $monthlyData = $this->formatChartData($this->getMonthlyData($church->id, $year));

        // Income by category - optimized single query with JOIN
        $incomeByCategoryRaw = TransactionCategory::where('transaction_categories.church_id', $church->id)
            ->forIncome()
            ->leftJoin('transactions', function ($join) use ($church, $year, $month) {
                $join->on('transaction_categories.id', '=', 'transactions.category_id')
                    ->where('transactions.church_id', $church->id)
                    ->where('transactions.direction', 'in')
                    ->where('transactions.status', 'completed')
                    ->whereNull('transactions.deleted_at');
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

        $incomeByCategory = $incomeByCategoryRaw->filter(fn ($c) => $c->total_amount > 0);

        // Expense by category - optimized single query with JOIN
        $expenseByCategoryRaw = TransactionCategory::where('transaction_categories.church_id', $church->id)
            ->forExpense()
            ->leftJoin('transactions', function ($join) use ($church, $year, $month) {
                $join->on('transaction_categories.id', '=', 'transactions.category_id')
                    ->where('transactions.church_id', $church->id)
                    ->where('transactions.direction', 'out')
                    ->where('transactions.status', 'completed')
                    ->whereNull('transactions.deleted_at');
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

        $expenseByCategory = $expenseByCategoryRaw->filter(fn ($c) => $c->total_amount > 0);

        // Expense by ministry - optimized single query with JOIN
        $expenseByMinistryRaw = Ministry::where('ministries.church_id', $church->id)
            ->leftJoin('transactions', function ($join) use ($church, $year, $month) {
                $join->on('ministries.id', '=', 'transactions.ministry_id')
                    ->where('transactions.church_id', $church->id)
                    ->where('transactions.direction', 'out')
                    ->where('transactions.status', 'completed')
                    ->whereNull('transactions.deleted_at')
                    ->where('transactions.source_type', '!=', Transaction::SOURCE_ALLOCATION);
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

        $expenseByMinistry = $expenseByMinistryRaw->filter(fn ($m) => $m->total_expense > 0);

        // Year comparison
        $yearComparison = $this->getYearComparison($church->id, $year);

        // === NEW: Quick Stats ===
        $quickStats = $this->getQuickStats($church->id);

        // === NEW: Active Campaigns ===
        $activeCampaigns = DonationCampaign::where('church_id', $church->id)
            ->where('is_active', true)
            ->orderByDesc('start_date')
            ->limit(3)
            ->get();

        // === NEW: Payment Methods Breakdown ===
        $paymentMethodsQuery = Transaction::where('church_id', $church->id)
            ->incoming()
            ->completed()
            ->whereNotIn('source_type', $excludeTypes);

        if ($month) {
            $paymentMethodsQuery->forMonth($year, $month);
        } else {
            $paymentMethodsQuery->forYear($year);
        }

        $paymentMethods = $paymentMethodsQuery
            ->selectRaw('payment_method, COUNT(*) as count, SUM(COALESCE(amount_uah, amount)) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($pm) => [
                'method' => $pm->payment_method,
                'label' => Transaction::PAYMENT_METHODS[$pm->payment_method] ?? $pm->payment_method ?? __('messages.other'),
                'count' => $pm->count,
                'total' => $pm->total,
            ]);

        return view('finances.index', compact(
            'church', 'year', 'month', 'periodLabel',
            'totalIncome', 'totalExpense', 'periodBalance',
            'initialBalance', 'initialBalanceDate', 'currentBalance',
            'allTimeIncome', 'allTimeExpense',
            'committedToTeams', 'availableBalance',
            'incomeByCurrency', 'expenseByCurrency', 'balancesByCurrency',
            'allTimeIncomeByCurrency', 'allTimeExpenseByCurrency',
            'initialBalances',
            'exchangeRates', 'enabledCurrencies',
            'monthlyData',
            'incomeByCategory', 'expenseByCategory', 'expenseByMinistry',
            'yearComparison',
            'quickStats', 'activeCampaigns', 'paymentMethods'
        ));
    }

    /**
     * Export journal to Excel
     */
    public function journalExport(Request $request)
    {
        if (! auth()->user()->canView('finances')) {
            abort(403, __('messages.no_access_to_finance_data'));
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
        $this->logAuditAction('exported', 'Transaction', null, __('messages.export_finance_journal'), [
            'period' => $period,
            'start_date' => $dates['start']->format('Y-m-d'),
            'end_date' => $dates['end']->format('Y-m-d'),
            'count' => $transactions->count(),
        ]);

        $filename = 'journal_'.$dates['start']->format('Y-m-d').'_'.$dates['end']->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            // BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [__('messages.csv_date'), __('messages.csv_type'), __('messages.csv_category'), __('messages.csv_team'), __('messages.csv_description'), __('messages.csv_person'), __('messages.csv_amount'), __('messages.csv_currency'), __('messages.csv_payment_method')], ';');

            foreach ($transactions as $t) {
                fputcsv($file, [
                    $t->date->format('d.m.Y'),
                    $t->direction === 'in' ? __('messages.direction_income') : __('messages.direction_expense'),
                    $t->category?->name ?? '-',
                    $t->ministry?->name ?? '-',
                    $t->description,
                    $t->person ? $t->person->first_name.' '.$t->person->last_name : '-',
                    ($t->direction === 'in' ? '+' : '-').number_format($t->amount, 2, ',', ''),
                    $t->currency ?? 'UAH',
                    Transaction::PAYMENT_METHODS[$t->payment_method] ?? $t->payment_method ?? '-',
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Unified Transactions page (replaces journal + incomes + expenses)
     */
    public function transactions(Request $request)
    {
        if (! auth()->user()->canView('finances')) {
            return $this->errorResponse($request, __('messages.no_access_to_section'));
        }

        $church = $this->getCurrentChurch();

        // Load ALL completed transactions for client-side period switching
        $transactions = Transaction::where('church_id', $church->id)
            ->completed()
            ->with(['category', 'person', 'ministry', 'recorder', 'attachments'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Balance before all transactions = church initial balance (multi-currency, converted to UAH)
        $initialBalanceDate = $church->initial_balance_date;
        $allInitialBalances = $church->getAllInitialBalances();
        $balanceBeforeYear = 0;
        foreach ($allInitialBalances as $currency => $amount) {
            if ($currency === 'UAH') {
                $balanceBeforeYear += $amount;
            } else {
                $balanceBeforeYear += ExchangeRate::toUah($amount, $currency, $initialBalanceDate);
            }
        }

        // Get filter options
        $categories = TransactionCategory::where('church_id', $church->id)->orderBy('name')->get();
        $ministries = Ministry::where('church_id', $church->id)->orderBy('name')->get();
        $people = Person::where('church_id', $church->id)
            ->whereHas('transactions')
            ->orderBy('first_name')
            ->get();

        // Current balance (all time) - in UAH, excluding exchange/allocation (they net to ~0)
        $excludeTypes = ['exchange', 'allocation'];
        $currentBalance = $balanceBeforeYear
            + (Transaction::where('church_id', $church->id)->incoming()->completed()
                ->whereNotIn('source_type', $excludeTypes)
                ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')->value('total') ?? 0)
            - (Transaction::where('church_id', $church->id)->outgoing()->completed()
                ->whereNotIn('source_type', $excludeTypes)
                ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')->value('total') ?? 0);

        // Initial period from request or default to month
        $initialPeriod = $request->get('period', 'month');
        $initialFilter = match ($request->get('filter', '')) {
            'income' => 'in',
            'expense' => 'out',
            default => $request->get('filter', ''),
        };

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

        return view('finances.transactions', compact(
            'transactions', 'initialPeriod', 'initialFilter', 'balanceBeforeYear', 'currentBalance',
            'categories', 'ministries', 'people',
            'incomeCategories', 'expenseCategories', 'enabledCurrencies', 'exchangeRates'
        ));
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

        // Use multi-currency initial balances (same as index() and transactions())
        $allInitialBalances = $church->getAllInitialBalances();
        $initialBalance = 0;
        foreach ($allInitialBalances as $currency => $amount) {
            if ($currency === 'UAH') {
                $initialBalance += $amount;
            } else {
                $initialBalance += ExchangeRate::toUah($amount, $currency, $church->initial_balance_date);
            }
        }

        $excludeTypes = ['exchange', 'allocation'];
        $income = Transaction::where('church_id', $churchId)
            ->incoming()->completed()
            ->where('date', '<', $date)
            ->whereNotIn('source_type', $excludeTypes)
            ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')
            ->value('total') ?? 0;

        $expense = Transaction::where('church_id', $churchId)
            ->outgoing()->completed()
            ->where('date', '<', $date)
            ->whereNotIn('source_type', $excludeTypes)
            ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')
            ->value('total') ?? 0;

        return $initialBalance + $income - $expense;
    }

    public function createIncome(Request $request)
    {
        if (! auth()->user()->canCreate('finances')) {
            return $this->errorResponse($request, __('messages.no_permission_create'));
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
        if (! auth()->user()->canCreate('finances')) {
            return $this->errorResponse($request, __('messages.no_permission_create'), 403);
        }

        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:transaction_categories,id', new BelongsToChurch(TransactionCategory::class, 'income')],
            'category_name' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|in:UAH,USD,EUR',
            'date' => 'required|date',
            'person_id' => ['nullable', 'exists:people,id', new BelongsToChurch(Person::class)],
            'description' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,card',
            'is_anonymous' => 'boolean',
            'notes' => 'nullable|string|max:5000',
        ]);

        $church = $this->getCurrentChurch();

        // Resolve category: existing ID or create from custom name
        $categoryId = $validated['category_id'] ?? null;
        if (! $categoryId && ! empty($validated['category_name'])) {
            $cat = TransactionCategory::firstOrCreate([
                'church_id' => $church->id,
                'name' => trim($validated['category_name']),
                'type' => 'income',
            ], [
                'sort_order' => TransactionCategory::where('church_id', $church->id)->max('sort_order') + 1,
            ]);
            $categoryId = $cat->id;
        }

        if (! $categoryId) {
            return $this->errorResponse($request, __('messages.select_or_enter_category'), 422);
        }

        $validated['category_id'] = $categoryId;

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

        $this->clearFinanceCache($church->id);

        broadcast(new TransactionCreated(
            churchId: $church->id,
            type: 'income',
            amount: (float) $validated['amount'],
            currency: $validated['currency'] ?? 'UAH',
            category: $transaction->category?->name,
        ))->toOthers();
        broadcast(new ChurchDataUpdated($church->id, 'finances', 'created'))->toOthers();
        broadcast(new ChurchDataUpdated($church->id, 'dashboard', 'updated'))->toOthers();
        broadcast(new ChurchDataUpdated($church->id, 'budgets', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.income_added'), 'finances.transactions', ['filter' => 'income'], [
            'transaction' => $transaction->load('category'),
        ]);
    }

    public function editIncome(Request $request, Transaction $transaction)
    {
        $this->authorizeChurch($transaction);

        if (! auth()->user()->canEdit('finances')) {
            return $this->errorResponse($request, __('messages.no_permission_edit'), 403);
        }

        $church = $this->getCurrentChurch();
        $categories = TransactionCategory::where('church_id', $church->id)
            ->forIncome()
            ->orderBy('sort_order')
            ->get();
        $enabledCurrencies = CurrencyHelper::getEnabledCurrencies($church->enabled_currencies);
        $exchangeRates = ExchangeRate::getLatestRates();
        $income = $transaction;

        if ($request->wantsJson()) {
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
        $this->authorizeChurch($transaction);

        if (! auth()->user()->canEdit('finances')) {
            return $this->errorResponse($request, __('messages.no_permission_edit'), 403);
        }

        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:transaction_categories,id', new BelongsToChurch(TransactionCategory::class, 'income')],
            'category_name' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|in:UAH,USD,EUR',
            'date' => 'required|date',
            'person_id' => ['nullable', 'exists:people,id', new BelongsToChurch(Person::class)],
            'description' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,card',
            'is_anonymous' => 'boolean',
            'notes' => 'nullable|string|max:5000',
        ]);

        // Resolve category: existing ID or create from custom name
        $church = $this->getCurrentChurch();
        $categoryId = $validated['category_id'] ?? null;
        if (! $categoryId && ! empty($validated['category_name'])) {
            $cat = TransactionCategory::firstOrCreate([
                'church_id' => $church->id,
                'name' => trim($validated['category_name']),
                'type' => 'income',
            ], [
                'sort_order' => TransactionCategory::where('church_id', $church->id)->max('sort_order') + 1,
            ]);
            $categoryId = $cat->id;
        }

        if (! $categoryId) {
            return $this->errorResponse($request, __('messages.select_or_enter_category'), 422);
        }

        $validated['category_id'] = $categoryId;

        // Recalculate source_type based on new category
        $category = TransactionCategory::find($categoryId);
        $sourceType = 'income';
        if ($category->is_tithe) {
            $sourceType = Transaction::SOURCE_TITHE;
        } elseif ($category->is_offering) {
            $sourceType = Transaction::SOURCE_OFFERING;
        } elseif ($category->is_donation) {
            $sourceType = Transaction::SOURCE_DONATION;
        }
        $validated['source_type'] = $sourceType;

        $validated['is_anonymous'] = $request->boolean('is_anonymous');
        if ($validated['is_anonymous']) {
            $validated['person_id'] = null;
        }
        $validated['currency'] = $validated['currency'] ?? 'UAH';

        $transaction->update($validated);

        $this->clearFinanceCache($transaction->church_id);

        broadcast(new ChurchDataUpdated($transaction->church_id, 'finances', 'updated'))->toOthers();
        broadcast(new ChurchDataUpdated($transaction->church_id, 'dashboard', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.income_updated'), 'finances.transactions', ['filter' => 'income'], [
            'transaction' => $transaction->fresh()->load('category'),
        ]);
    }

    public function destroyIncome(Request $request, Transaction $transaction)
    {
        $this->authorizeChurch($transaction);

        if (! auth()->user()->canDelete('finances')) {
            return $this->errorResponse($request, __('messages.no_permission_delete'), 403);
        }

        $transaction->delete();

        $this->clearFinanceCache($transaction->church_id);

        broadcast(new ChurchDataUpdated($transaction->church_id, 'finances', 'deleted'))->toOthers();
        broadcast(new ChurchDataUpdated($transaction->church_id, 'dashboard', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.income_deleted'), 'finances.transactions', ['filter' => 'income']);
    }

    public function createExpense(Request $request)
    {
        if (! auth()->user()->canCreate('finances')) {
            return $this->errorResponse($request, __('messages.no_permission_create'));
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
        if (! auth()->user()->canCreate('finances')) {
            return $this->errorResponse($request, __('messages.no_permission_create'), 403);
        }

        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:transaction_categories,id', new BelongsToChurch(TransactionCategory::class, 'expense')],
            'category_name' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|in:UAH,USD,EUR',
            'date' => 'required|date',
            'ministry_id' => ['nullable', 'exists:ministries,id', new BelongsToChurch(Ministry::class)],
            'description' => 'required|string|max:255',
            'payment_method' => 'nullable|in:cash,card',
            'expense_type' => 'nullable|in:recurring,one_time',
            'notes' => 'nullable|string|max:5000',
            'force_over_budget' => 'boolean',
            'receipts' => 'nullable|array|max:10',
            'receipts.*' => 'file|mimes:jpg,jpeg,png,gif,webp,heic,heif,pdf|max:10240',
        ]);

        $church = $this->getCurrentChurch();

        // Resolve category: existing ID or create from custom name
        if (empty($validated['category_id']) && ! empty($validated['category_name'])) {
            $cat = TransactionCategory::firstOrCreate([
                'church_id' => $church->id,
                'name' => trim($validated['category_name']),
                'type' => 'expense',
            ], [
                'sort_order' => TransactionCategory::where('church_id', $church->id)->max('sort_order') + 1,
            ]);
            $validated['category_id'] = $cat->id;
        }

        // Check ministry budget limits
        $budgetWarning = null;
        if (! empty($validated['ministry_id'])) {
            $ministry = Ministry::find($validated['ministry_id']);
            if ($ministry) {
                $expenseAmountUah = (float) $validated['amount'];
                $currency = $validated['currency'] ?? 'UAH';
                if ($currency !== 'UAH') {
                    $expenseAmountUah = ExchangeRate::toUah($expenseAmountUah, $currency, $validated['date'] ?? now()->toDateString());
                }
                $budgetCheck = $ministry->canAddExpense($expenseAmountUah, $validated['date'] ?? null);

                if (! $budgetCheck['allowed'] && ! $request->boolean('force_over_budget')) {
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

        $message = __('messages.expense_added');
        if ($budgetWarning) {
            $message .= ' '.$budgetWarning;
        }

        $this->clearFinanceCache($church->id);

        broadcast(new TransactionCreated(
            churchId: $church->id,
            type: 'expense',
            amount: (float) $validated['amount'],
            currency: $validated['currency'] ?? 'UAH',
            category: $transaction->category?->name,
        ))->toOthers();
        broadcast(new ChurchDataUpdated($church->id, 'finances', 'created'))->toOthers();
        broadcast(new ChurchDataUpdated($church->id, 'dashboard', 'updated'))->toOthers();
        broadcast(new ChurchDataUpdated($church->id, 'budgets', 'updated'))->toOthers();

        // Determine redirect target
        $redirectToMinistry = ! empty($validated['ministry_id']) && $request->input('redirect_to') === 'ministry';
        $routeName = $redirectToMinistry ? 'ministries.show' : 'finances.transactions';
        $routeParams = $redirectToMinistry ? ['ministry' => $validated['ministry_id'], 'tab' => 'expenses'] : ['filter' => 'expense'];

        return $this->successResponse($request, $message, $routeName, $routeParams, [
            'transaction' => $transaction->load(['category', 'ministry']),
            'budget_warning' => $budgetWarning,
        ]);
    }

    public function editExpense(Request $request, Transaction $transaction)
    {
        $this->authorizeChurch($transaction);

        if (! auth()->user()->canEdit('finances')) {
            return $this->errorResponse($request, __('messages.no_permission_edit'), 403);
        }

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

        if ($request->wantsJson()) {
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
        $this->authorizeChurch($transaction);

        if (! auth()->user()->canEdit('finances')) {
            return $this->errorResponse($request, __('messages.no_permission_edit'), 403);
        }

        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:transaction_categories,id', new BelongsToChurch(TransactionCategory::class, 'expense')],
            'category_name' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|in:UAH,USD,EUR',
            'date' => 'required|date',
            'ministry_id' => ['nullable', 'exists:ministries,id', new BelongsToChurch(Ministry::class)],
            'description' => 'required|string|max:255',
            'payment_method' => 'nullable|in:cash,card',
            'expense_type' => 'nullable|in:recurring,one_time',
            'notes' => 'nullable|string|max:5000',
            'force_over_budget' => 'boolean',
            'receipts' => 'nullable|array|max:10',
            'receipts.*' => 'file|mimes:jpg,jpeg,png,gif,webp,heic,heif,pdf|max:10240',
            'delete_attachments' => 'nullable|array',
            'delete_attachments.*' => 'integer|exists:transaction_attachments,id',
        ]);

        // Resolve category: existing ID or create from custom name
        $church = $this->getCurrentChurch();
        if (empty($validated['category_id']) && ! empty($validated['category_name'])) {
            $cat = TransactionCategory::firstOrCreate([
                'church_id' => $church->id,
                'name' => trim($validated['category_name']),
                'type' => 'expense',
            ], [
                'sort_order' => TransactionCategory::where('church_id', $church->id)->max('sort_order') + 1,
            ]);
            $validated['category_id'] = $cat->id;
        }

        $validated['currency'] = $validated['currency'] ?? 'UAH';

        // Check ministry budget limits (only if amount increased or ministry changed)
        $budgetWarning = null;
        $newMinistryId = $validated['ministry_id'] ?? null;
        $newCurrency = $validated['currency'] ?? 'UAH';
        $newAmountUah = (float) $validated['amount'];
        if ($newCurrency !== 'UAH') {
            $newAmountUah = ExchangeRate::toUah($newAmountUah, $newCurrency, $validated['date'] ?? now()->toDateString());
        }
        $oldAmountUah = (float) ($transaction->amount_uah ?? $transaction->amount);

        // Check budget if: ministry changed to new one, or amount increased for same ministry
        if ($newMinistryId) {
            $ministry = Ministry::find($newMinistryId);
            if ($ministry) {
                // Calculate effective new expense for budget check (in UAH)
                $checkAmount = ($transaction->ministry_id === $newMinistryId)
                    ? $newAmountUah - $oldAmountUah  // Same ministry - only check the increase
                    : $newAmountUah;  // New ministry - check full amount

                if ($checkAmount > 0) {
                    $budgetCheck = $ministry->canAddExpense($checkAmount, $validated['date'] ?? null);

                    if (! $budgetCheck['allowed'] && ! $request->boolean('force_over_budget')) {
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
        if (! empty($validated['delete_attachments'])) {
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

        $message = __('messages.expense_updated');
        if ($budgetWarning) {
            $message .= ' '.$budgetWarning;
        }

        // Determine redirect target
        $redirectToMinistry = $request->input('redirect_to') === 'ministry' && $request->input('redirect_ministry_id');
        $routeName = $redirectToMinistry ? 'ministries.show' : 'finances.transactions';
        $routeParams = $redirectToMinistry ? ['ministry' => $request->input('redirect_ministry_id'), 'tab' => 'expenses'] : ['filter' => 'expense'];

        $this->clearFinanceCache($transaction->church_id);

        broadcast(new ChurchDataUpdated($transaction->church_id, 'finances', 'updated'))->toOthers();
        broadcast(new ChurchDataUpdated($transaction->church_id, 'dashboard', 'updated'))->toOthers();

        return $this->successResponse($request, $message, $routeName, $routeParams, [
            'transaction' => $transaction->fresh()->load(['category', 'ministry', 'attachments']),
            'budget_warning' => $budgetWarning,
        ]);
    }

    public function destroyExpense(Request $request, Transaction $transaction)
    {
        $this->authorizeChurch($transaction);

        if (! auth()->user()->canDelete('finances')) {
            return $this->errorResponse($request, __('messages.no_permission_delete'), 403);
        }
        $ministryId = $transaction->ministry_id;
        $churchId = $transaction->church_id;
        $transaction->delete();

        $this->clearFinanceCache($churchId);

        broadcast(new ChurchDataUpdated($churchId, 'finances', 'deleted'))->toOthers();
        broadcast(new ChurchDataUpdated($churchId, 'dashboard', 'updated'))->toOthers();

        // Determine redirect target
        if ($request->input('redirect_to') === 'ministry' && $ministryId) {
            return $this->successResponse($request, __('messages.expense_deleted'), 'ministries.show', ['ministry' => $ministryId, 'tab' => 'expenses']);
        }

        return $this->successResponse($request, __('messages.expense_deleted'));
    }

    // Currency Exchange
    public function createExchange(Request $request)
    {
        if (! auth()->user()->canCreate('finances')) {
            return $this->errorResponse($request, __('messages.no_permission_create'));
        }

        $church = $this->getCurrentChurch();
        $enabledCurrencies = CurrencyHelper::getEnabledCurrencies($church->enabled_currencies);
        $exchangeRates = ExchangeRate::getLatestRates();

        return view('finances.exchange.create', compact('enabledCurrencies', 'exchangeRates'));
    }

    public function storeExchange(Request $request)
    {
        if (! auth()->user()->canCreate('finances')) {
            abort(403, __('messages.no_permission_create'));
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
                'description' => __('messages.exchange_description', ['from' => $validated['from_currency'], 'to' => $validated['to_currency']]),
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
                'description' => __('messages.exchange_description', ['from' => $validated['from_currency'], 'to' => $validated['to_currency']]),
                'notes' => $validated['notes'],
                'related_transaction_id' => $outTransaction->id,
            ]);

            // Link back
            $outTransaction->update(['related_transaction_id' => $inTransaction->id]);
        });

        $this->clearFinanceCache($church->id);

        broadcast(new ChurchDataUpdated($church->id, 'finances', 'exchange'))->toOthers();
        broadcast(new ChurchDataUpdated($church->id, 'dashboard', 'updated'))->toOthers();

        return $this->successResponse($request, __('messages.exchange_registered'), 'finances.index');
    }

    // Categories (unified)
    public function categories()
    {
        return redirect()->route('finances.index');
    }

    public function storeCategory(Request $request)
    {
        if (! auth()->user()->canCreate('finances')) {
            abort(403, __('messages.no_permission_create_categories'));
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

        return $this->successResponse($request, __('messages.category_added'));
    }

    public function updateCategory(Request $request, TransactionCategory $category)
    {
        if (! auth()->user()->canEdit('finances')) {
            abort(403, __('messages.no_permission_edit_categories'));
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

        return $this->successResponse($request, __('messages.category_updated'));
    }

    public function destroyCategory(Request $request, TransactionCategory $category)
    {
        if (! auth()->user()->canDelete('finances')) {
            abort(403, __('messages.no_permission_delete_categories'));
        }

        if ($category->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }

        if ($category->transactions()->count() > 0) {
            return $this->errorResponse($request, __('messages.cannot_delete_category_with_transactions'));
        }

        $category->delete();

        return $this->successResponse($request, __('messages.category_deleted'));
    }

    // Team Budgets
    public function budgets(Request $request)
    {
        if (! auth()->user()->canView('finances')) {
            return $this->errorResponse($request, __('messages.no_access_to_section'));
        }

        $church = $this->getCurrentChurch();
        $year = max(2000, min(2100, (int) $request->get('year', now()->year)));
        $month = max(1, min(12, (int) $request->get('month', now()->month)));

        $ministries = Ministry::where('church_id', $church->id)
            ->with(['leader', 'budgets' => function ($q) use ($year, $month) {
                $q->where('year', $year)->where('month', $month);
            }, 'budgets.items.responsiblePeople', 'budgets.items.category'])
            ->orderBy('name')
            ->get();

        // Batch query: spending grouped by (ministry_id, category_id, budget_item_id)
        // Exclude allocation transactions — they are transfers, not real expenses
        $spendingRaw = Transaction::where('church_id', $church->id)
            ->where('direction', Transaction::DIRECTION_OUT)
            ->whereNotIn('source_type', [Transaction::SOURCE_ALLOCATION, Transaction::SOURCE_EXCHANGE])
            ->completed()
            ->forMonth($year, $month)
            ->whereNotNull('ministry_id')
            ->selectRaw('ministry_id, category_id, budget_item_id, SUM(COALESCE(amount_uah, amount)) as total')
            ->groupBy('ministry_id', 'category_id', 'budget_item_id')
            ->get();

        // Index spending data for fast lookup
        $spendingByMinistry = [];
        $spendingByBudgetItem = [];
        $spendingByCategoryUnlinked = []; // ministry_id => category_id => total (where budget_item_id IS NULL)

        foreach ($spendingRaw as $row) {
            $mid = $row->ministry_id;
            $spendingByMinistry[$mid] = ($spendingByMinistry[$mid] ?? 0) + $row->total;

            if ($row->budget_item_id) {
                $spendingByBudgetItem[$row->budget_item_id] = ($spendingByBudgetItem[$row->budget_item_id] ?? 0) + $row->total;
            } else {
                if ($row->category_id) {
                    $spendingByCategoryUnlinked[$mid][$row->category_id] = ($spendingByCategoryUnlinked[$mid][$row->category_id] ?? 0) + $row->total;
                } else {
                    $spendingByCategoryUnlinked[$mid][0] = ($spendingByCategoryUnlinked[$mid][0] ?? 0) + $row->total;
                }
            }
        }

        // Batch query: allocations grouped by ministry_id
        $allocationsByMinistry = Transaction::where('church_id', $church->id)
            ->where('direction', Transaction::DIRECTION_IN)
            ->where('source_type', Transaction::SOURCE_ALLOCATION)
            ->completed()
            ->forMonth($year, $month)
            ->whereNotNull('ministry_id')
            ->selectRaw('ministry_id, SUM(COALESCE(amount_uah, amount)) as total')
            ->groupBy('ministry_id')
            ->pluck('total', 'ministry_id');

        $ministries = $ministries->map(function ($ministry) use ($spendingByMinistry, $spendingByBudgetItem, $spendingByCategoryUnlinked, $allocationsByMinistry) {
            $budget = $ministry->budgets->first();
            $spent = $spendingByMinistry[$ministry->id] ?? 0;
            $allocated = (float) ($allocationsByMinistry[$ministry->id] ?? ($budget?->allocated_budget ?? 0));
            $effectiveBudget = $budget ? $budget->getEffectiveBudget() : 0;

            // Compute per-item spending
            $items = [];
            $itemsSpentTotal = 0;
            if ($budget && $budget->items->isNotEmpty()) {
                foreach ($budget->items as $item) {
                    $directSpent = $spendingByBudgetItem[$item->id] ?? 0;
                    $autoMatched = 0;
                    if ($item->category_id) {
                        $autoMatched = $spendingByCategoryUnlinked[$ministry->id][$item->category_id] ?? 0;
                    }
                    $itemSpent = $directSpent + $autoMatched;
                    $itemsSpentTotal += $itemSpent;
                    $monthlyPlanned = $item->getMonthlyPlanned();
                    $items[] = [
                        'id' => $item->id,
                        'name' => $item->name,
                        'category' => $item->category,
                        'category_id' => $item->category_id,
                        'planned_amount' => $monthlyPlanned,
                        'base_planned_amount' => (float) $item->planned_amount,
                        'frequency' => $item->frequency ?? 'one_time',
                        'actual' => $itemSpent,
                        'difference' => $monthlyPlanned - $itemSpent,
                        'responsible' => $item->responsiblePeople,
                        'notes' => $item->notes,
                        'sort_order' => $item->sort_order,
                    ];
                }
            }

            // Unmatched spending (not linked to any budget item and not auto-matched by category)
            $unmatchedSpent = $spent - $itemsSpentTotal;

            return [
                'ministry' => $ministry,
                'budget' => $budget,
                'monthly_budget' => $effectiveBudget,
                'allocated' => $allocated,
                'spent' => $spent,
                'remaining' => $effectiveBudget - $spent,
                'percentage' => $effectiveBudget > 0
                    ? round(($spent / $effectiveBudget) * 100, 1)
                    : 0,
                'items' => $items,
                'unmatched_spent' => max(0, $unmatchedSpent),
                'has_items' => ! empty($items),
            ];
        });

        $totals = [
            'budget' => $ministries->sum('monthly_budget'),
            'allocated' => $ministries->sum('allocated'),
            'spent' => $ministries->sum('spent'),
            'remaining' => $ministries->sum('remaining'),
        ];

        // Get recent expenses without receipts (categories with receipt_required)
        $expensesMissingReceipts = Transaction::where('church_id', $church->id)
            ->where('direction', Transaction::DIRECTION_OUT)
            ->whereHas('category', fn ($q) => $q->where('receipt_required', true))
            ->whereDoesntHave('attachments')
            ->forMonth($year, $month)
            ->with(['ministry', 'category'])
            ->orderByDesc('date')
            ->limit(10)
            ->get();

        // ── Church Budget ──
        $churchBudget = ChurchBudget::where('church_id', $church->id)
            ->where('year', $year)
            ->with(['items.category'])
            ->first();

        $churchBudgetItems = [];
        $churchBudgetTotals = ['planned' => 0, 'actual' => 0, 'difference' => 0, 'annual_planned' => 0, 'annual_actual' => 0];

        if ($churchBudget) {
            // Batch query: actual spending by category_id for this month (eliminates N+1)
            $categoryIds = $churchBudget->items->pluck('category_id')->filter()->unique()->values()->all();
            $actualByCategory = [];
            if (! empty($categoryIds)) {
                $actualByCategory = Transaction::where('church_id', $church->id)
                    ->where('direction', Transaction::DIRECTION_OUT)
                    ->whereIn('category_id', $categoryIds)
                    ->whereNotIn('source_type', [Transaction::SOURCE_ALLOCATION, Transaction::SOURCE_EXCHANGE])
                    ->completed()
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->groupBy('category_id')
                    ->selectRaw('category_id, SUM(COALESCE(amount_uah, amount)) as total')
                    ->pluck('total', 'category_id')
                    ->all();
            }

            foreach ($churchBudget->items as $item) {
                $planned = $item->getPlannedForMonth($month);
                $actual = $item->category_id ? (float) ($actualByCategory[$item->category_id] ?? 0) : 0;
                $annualPlanned = $item->getAnnualTotal();

                $churchBudgetItems[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category,
                    'category_id' => $item->category_id,
                    'is_recurring' => $item->is_recurring,
                    'amounts' => $item->amounts,
                    'planned' => $planned,
                    'actual' => $actual,
                    'difference' => $planned - $actual,
                    'annual_planned' => $annualPlanned,
                    'notes' => $item->notes,
                    'sort_order' => $item->sort_order,
                ];

                $churchBudgetTotals['planned'] += $planned;
                $churchBudgetTotals['actual'] += $actual;
                $churchBudgetTotals['annual_planned'] += $annualPlanned;
            }
            $churchBudgetTotals['difference'] = $churchBudgetTotals['planned'] - $churchBudgetTotals['actual'];
            $churchBudgetTotals['annual_actual'] = $churchBudget->getActualSpendingForYear();
        }

        // Grand total: use total actual spending to avoid double-counting
        // (transactions with ministry_id may also match church budget categories)
        $totalActualSpending = (float) (Transaction::where('church_id', $church->id)
            ->where('direction', Transaction::DIRECTION_OUT)
            ->whereNotIn('source_type', [Transaction::SOURCE_ALLOCATION, Transaction::SOURCE_EXCHANGE])
            ->completed()
            ->forMonth($year, $month)
            ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')
            ->value('total') ?? 0);

        $grandTotals = [
            'planned' => $churchBudgetTotals['planned'] + $totals['budget'],
            'actual' => round($totalActualSpending),
        ];
        $grandTotals['difference'] = $grandTotals['planned'] - $grandTotals['actual'];
        $grandTotals['percentage'] = $grandTotals['planned'] > 0
            ? round(($grandTotals['actual'] / $grandTotals['planned']) * 100, 1)
            : 0;

        // Separate percentages for tab indicators
        $churchBudgetTotals['percentage'] = $churchBudgetTotals['planned'] > 0
            ? round(($churchBudgetTotals['actual'] / $churchBudgetTotals['planned']) * 100, 1)
            : 0;
        $ministryPercentage = $totals['budget'] > 0
            ? round(($totals['spent'] / $totals['budget']) * 100, 1)
            : 0;

        // Sort ministries: overspend first (negative remaining = overspend)
        $ministries = $ministries->sortBy('remaining')->values();

        // ── Trend data: last 6 months plan vs fact ──
        $trendData = $this->getBudgetTrendData($church, $year, $month);

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
            'exchangeRates',
            'churchBudget',
            'churchBudgetItems',
            'churchBudgetTotals',
            'grandTotals',
            'trendData',
            'ministryPercentage'
        ));
    }

    /**
     * Get plan vs fact trend data for last 6 months
     */
    private function getBudgetTrendData($church, int $currentYear, int $currentMonth): array
    {
        $months = [];
        $date = Carbon::create($currentYear, $currentMonth, 1);

        for ($i = 5; $i >= 0; $i--) {
            $d = $date->copy()->subMonths($i);
            $y = $d->year;
            $m = $d->month;

            // Ministry planned: sum of effective budgets
            $ministryBudgets = MinistryBudget::where('church_id', $church->id)
                ->where('year', $y)->where('month', $m)
                ->with('items')
                ->get();

            $ministryPlanned = 0;
            foreach ($ministryBudgets as $mb) {
                $ministryPlanned += $mb->getEffectiveBudget();
            }

            // Total actual spending for the church (all expenses, excluding exchange/allocation)
            $totalActual = (float) (Transaction::where('church_id', $church->id)
                ->where('direction', Transaction::DIRECTION_OUT)
                ->whereNotIn('source_type', [Transaction::SOURCE_ALLOCATION, Transaction::SOURCE_EXCHANGE])
                ->completed()
                ->forMonth($y, $m)
                ->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')
                ->value('total') ?? 0);

            // Church planned
            $cb = ChurchBudget::where('church_id', $church->id)->where('year', $y)->with('items')->first();
            $churchPlanned = 0;
            if ($cb) {
                foreach ($cb->items as $item) {
                    $churchPlanned += $item->getPlannedForMonth($m);
                }
            }

            $months[] = [
                'label' => __('messages.month_short_'.$m),
                'year' => $y,
                'month' => $m,
                'planned' => round($churchPlanned + $ministryPlanned),
                'actual' => round($totalActual),
            ];
        }

        return $months;
    }

    /**
     * Copy all ministry budgets from one month to another
     */
    public function copyAllBudgets(Request $request)
    {
        if (! auth()->user()->canEdit('finances')) {
            abort(403);
        }

        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'from_year' => 'required|integer|min:2020|max:2100',
            'from_month' => 'required|integer|min:1|max:12',
            'to_year' => 'required|integer|min:2020|max:2100',
            'to_month' => 'required|integer|min:1|max:12',
        ]);

        $sourceBudgets = MinistryBudget::where('church_id', $church->id)
            ->where('year', $validated['from_year'])
            ->where('month', $validated['from_month'])
            ->with('items.responsiblePeople')
            ->get();

        if ($sourceBudgets->isEmpty()) {
            return response()->json(['success' => false, 'message' => __('messages.no_budgets_to_copy')], 422);
        }

        $copied = 0;
        $skipped = 0;

        foreach ($sourceBudgets as $source) {
            // Check if target already has items
            $target = MinistryBudget::where('church_id', $church->id)
                ->where('ministry_id', $source->ministry_id)
                ->where('year', $validated['to_year'])
                ->where('month', $validated['to_month'])
                ->first();

            if ($target && $target->items()->count() > 0) {
                $skipped++;

                continue;
            }

            if (! $target) {
                $target = MinistryBudget::create([
                    'church_id' => $church->id,
                    'ministry_id' => $source->ministry_id,
                    'year' => $validated['to_year'],
                    'month' => $validated['to_month'],
                    'monthly_budget' => $source->monthly_budget,
                ]);
            } else {
                $target->update(['monthly_budget' => $source->monthly_budget]);
            }

            foreach ($source->items as $item) {
                $newItem = BudgetItem::create([
                    'church_id' => $church->id,
                    'ministry_budget_id' => $target->id,
                    'category_id' => $item->category_id,
                    'name' => $item->name,
                    'planned_amount' => $item->planned_amount,
                    'frequency' => $item->frequency,
                    'notes' => $item->notes,
                    'sort_order' => $item->sort_order,
                ]);
                if ($item->responsiblePeople->isNotEmpty()) {
                    $newItem->responsiblePeople()->attach($item->responsiblePeople->pluck('id'));
                }
            }

            $copied++;
        }

        $monthName = __('messages.month_short_'.$validated['to_month']);
        $msg = __('messages.budgets_copied', ['copied' => $copied, 'month' => $monthName, 'year' => $validated['to_year']]);
        if ($skipped > 0) {
            $msg .= ' '.__('messages.budgets_skipped', ['skipped' => $skipped]);
        }

        return response()->json(['success' => true, 'message' => $msg]);
    }

    public function updateBudget(Request $request, Ministry $ministry)
    {
        if (! auth()->user()->canEdit('finances')) {
            abort(403, __('messages.no_permission_edit_budgets'));
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

        return $this->successResponse($request, __('messages.budget_updated_for', ['name' => $ministry->name]));
    }

    public function allocateBudget(Request $request, Ministry $ministry)
    {
        if (! auth()->user()->canEdit('finances')) {
            abort(403, __('messages.no_permission_allocate_budget'));
        }

        $church = $this->getCurrentChurch();

        if ($ministry->church_id !== $church->id) {
            abort(404);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|max:3',
            'payment_method' => 'nullable|string|max:50',
            'date' => 'required|date',
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'notes' => 'nullable|string|max:500',
        ]);

        $description = __('messages.budget_allocation_description', ['name' => $ministry->name]);

        DB::beginTransaction();
        try {
            // OUT transaction: from church general fund
            $outTransaction = Transaction::create([
                'church_id' => $church->id,
                'direction' => Transaction::DIRECTION_OUT,
                'source_type' => Transaction::SOURCE_ALLOCATION,
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'date' => $validated['date'],
                'description' => $description,
                'ministry_id' => $ministry->id,
                'payment_method' => $validated['payment_method'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => Transaction::STATUS_COMPLETED,
            ]);

            // IN transaction: into ministry budget
            $inTransaction = Transaction::create([
                'church_id' => $church->id,
                'direction' => Transaction::DIRECTION_IN,
                'source_type' => Transaction::SOURCE_ALLOCATION,
                'amount' => $validated['amount'],
                'currency' => $validated['currency'],
                'date' => $validated['date'],
                'description' => $description,
                'ministry_id' => $ministry->id,
                'payment_method' => $validated['payment_method'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => Transaction::STATUS_COMPLETED,
                'related_transaction_id' => $outTransaction->id,
            ]);

            // Link OUT → IN
            $outTransaction->update(['related_transaction_id' => $inTransaction->id]);

            // Update ministry budget allocated amount (use amount_uah computed by model)
            $budget = MinistryBudget::getOrCreate(
                $church->id,
                $ministry->id,
                $validated['year'],
                $validated['month']
            );

            $budget->increment('allocated_budget', $inTransaction->amount_uah);

            // If monthly_budget is 0 and no items, auto-set it to allocated
            if ($budget->monthly_budget == 0 && $budget->items()->count() === 0) {
                $budget->update(['monthly_budget' => $budget->allocated_budget]);
            }

            DB::commit();

            return $this->successResponse($request, __('messages.budget_allocated', ['amount' => number_format($validated['amount'], 0, ',', ' '), 'currency' => $validated['currency'], 'name' => $ministry->name]));
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => __('messages.budget_allocation_error', ['error' => $e->getMessage()])], 500);
        }
    }

    // Budget Items CRUD
    public function storeBudgetItem(Request $request, MinistryBudget $ministryBudget)
    {
        if (! auth()->user()->canEdit('finances')) {
            abort(403);
        }

        $church = $this->getCurrentChurch();
        if ($ministryBudget->church_id !== $church->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'planned_amount' => 'required|numeric|min:0',
            'frequency' => 'nullable|string|in:one_time,weekly,monthly',
            'category_id' => 'nullable|integer|exists:transaction_categories,id',
            'category_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'person_ids' => 'nullable|array',
            'person_ids.*' => 'integer|exists:people,id',
        ]);

        // Resolve category: existing ID or create from custom name
        $categoryId = $validated['category_id'] ?? null;
        if (! $categoryId && ! empty($validated['category_name'])) {
            $cat = TransactionCategory::firstOrCreate([
                'church_id' => $church->id,
                'name' => trim($validated['category_name']),
                'type' => 'expense',
            ], [
                'sort_order' => TransactionCategory::where('church_id', $church->id)->max('sort_order') + 1,
            ]);
            $categoryId = $cat->id;
        }

        // Check category uniqueness within this budget
        if ($categoryId) {
            $exists = BudgetItem::where('ministry_budget_id', $ministryBudget->id)
                ->where('category_id', $categoryId)
                ->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.budget_category_already_used'),
                ], 422);
            }
        }

        $maxSort = BudgetItem::where('ministry_budget_id', $ministryBudget->id)->max('sort_order') ?? 0;

        $item = BudgetItem::create([
            'church_id' => $church->id,
            'ministry_budget_id' => $ministryBudget->id,
            'category_id' => $categoryId,
            'name' => $validated['name'],
            'planned_amount' => $validated['planned_amount'],
            'frequency' => $validated['frequency'] ?? 'one_time',
            'notes' => $validated['notes'] ?? null,
            'sort_order' => $maxSort + 1,
        ]);

        if (! empty($validated['person_ids'])) {
            $item->responsiblePeople()->attach($validated['person_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.budget_item_added'),
            'item' => $item->load('responsiblePeople', 'category'),
        ]);
    }

    public function updateBudgetItem(Request $request, BudgetItem $budgetItem)
    {
        if (! auth()->user()->canEdit('finances')) {
            abort(403);
        }

        $church = $this->getCurrentChurch();
        if ($budgetItem->church_id !== $church->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'planned_amount' => 'required|numeric|min:0',
            'frequency' => 'nullable|string|in:one_time,weekly,monthly',
            'category_id' => 'nullable|integer|exists:transaction_categories,id',
            'category_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'person_ids' => 'nullable|array',
            'person_ids.*' => 'integer|exists:people,id',
        ]);

        // Resolve category: existing ID or create from custom name
        $categoryId = $validated['category_id'] ?? null;
        if (! $categoryId && ! empty($validated['category_name'])) {
            $cat = TransactionCategory::firstOrCreate([
                'church_id' => $church->id,
                'name' => trim($validated['category_name']),
                'type' => 'expense',
            ], [
                'sort_order' => TransactionCategory::where('church_id', $church->id)->max('sort_order') + 1,
            ]);
            $categoryId = $cat->id;
        }

        // Check category uniqueness (excluding self)
        if ($categoryId) {
            $exists = BudgetItem::where('ministry_budget_id', $budgetItem->ministry_budget_id)
                ->where('category_id', $categoryId)
                ->where('id', '!=', $budgetItem->id)
                ->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.budget_category_already_used'),
                ], 422);
            }
        }

        $budgetItem->update([
            'name' => $validated['name'],
            'planned_amount' => $validated['planned_amount'],
            'frequency' => $validated['frequency'] ?? 'one_time',
            'category_id' => $categoryId,
            'notes' => $validated['notes'] ?? null,
        ]);

        $budgetItem->responsiblePeople()->sync($validated['person_ids'] ?? []);

        return response()->json([
            'success' => true,
            'message' => __('messages.budget_item_updated'),
            'item' => $budgetItem->load('responsiblePeople', 'category'),
        ]);
    }

    public function destroyBudgetItem(Request $request, BudgetItem $budgetItem)
    {
        if (! auth()->user()->canEdit('finances')) {
            abort(403);
        }

        $church = $this->getCurrentChurch();
        if ($budgetItem->church_id !== $church->id) {
            abort(404);
        }

        // Unlink transactions from this budget item
        Transaction::where('budget_item_id', $budgetItem->id)->update(['budget_item_id' => null]);

        $budgetItem->delete();

        return response()->json([
            'success' => true,
            'message' => __('messages.budget_item_deleted'),
        ]);
    }

    public function budgetItemTransactions(Request $request, BudgetItem $budgetItem)
    {
        if (! auth()->user()->canView('finances')) {
            abort(403);
        }

        $church = $this->getCurrentChurch();
        if ($budgetItem->church_id !== $church->id) {
            abort(404);
        }

        $transactions = $budgetItem->getMatchedTransactions();

        return response()->json([
            'success' => true,
            'transactions' => $transactions->map(function ($t) {
                return [
                    'id' => $t->id,
                    'date' => $t->date->format('d.m.Y'),
                    'description' => $t->description,
                    'amount' => (float) ($t->amount_uah ?? $t->amount),
                    'currency' => $t->currency,
                    'category' => $t->category?->name,
                    'attachments' => $t->attachments->map(fn ($a) => [
                        'id' => $a->id,
                        'name' => $a->original_name,
                        'url' => $a->url,
                        'is_image' => $a->is_image,
                    ]),
                ];
            }),
        ]);
    }

    // Analytics API
    public function chartData(Request $request)
    {
        if (! auth()->user()->canView('finances')) {
            abort(403, __('messages.no_access_to_finance_data'));
        }

        $church = $this->getCurrentChurch();
        $year = (int) $request->get('year', now()->year);
        $month = $request->get('month') ? (int) $request->get('month') : null;
        $period = $request->get('period', 'year');

        $data = match ($period) {
            'month' => $this->getDailyData($church->id, $year, $month ?? (int) now()->month),
            'quarter' => $this->getQuarterData($church->id, $year, $month),
            'year' => $this->getMonthlyData($church->id, $year),
            'all' => $this->getAllTimeData($church->id),
            default => $this->getMonthlyData($church->id, $year),
        };

        return response()->json($this->formatChartData($data));
    }

    /**
     * Transform array of {month, income, expense} objects into {labels, income, expense} arrays for Chart.js
     */
    private function formatChartData(array $items): array
    {
        return [
            'labels' => array_column($items, 'month'),
            'income' => array_column($items, 'income'),
            'expense' => array_column($items, 'expense'),
        ];
    }

    // Private helpers
    private function getMonthlyData(int $churchId, int $year): array
    {
        // Optimized: single query with groupBy instead of 24 queries
        // Exclude exchange/allocation to avoid inflating income/expense
        $monthlyRaw = Transaction::where('church_id', $churchId)
            ->completed()
            ->whereYear('date', $year)
            ->whereNotIn('source_type', [Transaction::SOURCE_EXCHANGE, Transaction::SOURCE_ALLOCATION])
            ->selectRaw('MONTH(date) as month, direction, SUM(COALESCE(amount_uah, amount)) as total')
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

    private function getDailyData(int $churchId, int $year, int $month): array
    {
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        $dailyRaw = Transaction::where('church_id', $churchId)
            ->completed()
            ->whereNotIn('source_type', [Transaction::SOURCE_EXCHANGE, Transaction::SOURCE_ALLOCATION])
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->selectRaw('DAY(date) as day, direction, SUM(COALESCE(amount_uah, amount)) as total')
            ->groupBy('day', 'direction')
            ->get();

        $grouped = $dailyRaw->groupBy('day');

        $days = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dayData = $grouped[$d] ?? collect();
            $income = (float) $dayData->where('direction', 'in')->sum('total');
            $expense = (float) $dayData->where('direction', 'out')->sum('total');

            $days[] = [
                'month' => $d,
                'income' => $income,
                'expense' => $expense,
                'balance' => $income - $expense,
            ];
        }

        return $days;
    }

    private function getQuarterData(int $churchId, int $year, ?int $month): array
    {
        $quarter = $month ? (int) ceil($month / 3) : (int) ceil(now()->month / 3);
        $startMonth = ($quarter - 1) * 3 + 1;
        $endMonth = $quarter * 3;

        $monthlyRaw = Transaction::where('church_id', $churchId)
            ->completed()
            ->whereNotIn('source_type', [Transaction::SOURCE_EXCHANGE, Transaction::SOURCE_ALLOCATION])
            ->whereYear('date', $year)
            ->whereRaw('MONTH(date) BETWEEN ? AND ?', [$startMonth, $endMonth])
            ->selectRaw('MONTH(date) as month, direction, SUM(COALESCE(amount_uah, amount)) as total')
            ->groupBy('month', 'direction')
            ->get();

        $grouped = $monthlyRaw->groupBy('month');

        $months = [];
        for ($m = $startMonth; $m <= $endMonth; $m++) {
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

    private function getAllTimeData(int $churchId): array
    {
        $yearlyRaw = Transaction::where('church_id', $churchId)
            ->completed()
            ->whereNotIn('source_type', [Transaction::SOURCE_EXCHANGE, Transaction::SOURCE_ALLOCATION])
            ->selectRaw('YEAR(date) as year, direction, SUM(COALESCE(amount_uah, amount)) as total')
            ->groupBy('year', 'direction')
            ->get();

        $grouped = $yearlyRaw->groupBy('year');

        if ($grouped->isEmpty()) {
            return [['month' => (string) now()->year, 'income' => 0, 'expense' => 0, 'balance' => 0]];
        }

        $minYear = $grouped->keys()->min();
        $maxYear = $grouped->keys()->max();

        $years = [];
        for ($y = $minYear; $y <= $maxYear; $y++) {
            $yearData = $grouped[$y] ?? collect();
            $income = (float) $yearData->where('direction', 'in')->sum('total');
            $expense = (float) $yearData->where('direction', 'out')->sum('total');

            $years[] = [
                'month' => (string) $y,
                'income' => $income,
                'expense' => $expense,
                'balance' => $income - $expense,
            ];
        }

        return $years;
    }

    /**
     * Clear cached finance dashboard data for a church.
     */
    private function clearFinanceCache(int $churchId): void
    {
        Cache::forget('finance_alltime_' . $churchId);
        Cache::forget('finance_currency_' . $churchId);
    }

    private function getYearComparison(int $churchId, int $year): array
    {
        $excludeTypes = [Transaction::SOURCE_EXCHANGE, Transaction::SOURCE_ALLOCATION];
        $currentYear = [
            'income' => Transaction::where('church_id', $churchId)->incoming()->completed()->whereNotIn('source_type', $excludeTypes)->forYear($year)->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')->value('total') ?? 0,
            'expense' => Transaction::where('church_id', $churchId)->outgoing()->completed()->whereNotIn('source_type', $excludeTypes)->forYear($year)->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')->value('total') ?? 0,
        ];
        $currentYear['balance'] = $currentYear['income'] - $currentYear['expense'];

        $prevYear = [
            'income' => Transaction::where('church_id', $churchId)->incoming()->completed()->whereNotIn('source_type', $excludeTypes)->forYear($year - 1)->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')->value('total') ?? 0,
            'expense' => Transaction::where('church_id', $churchId)->outgoing()->completed()->whereNotIn('source_type', $excludeTypes)->forYear($year - 1)->selectRaw('SUM(COALESCE(amount_uah, amount)) as total')->value('total') ?? 0,
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
        return __('messages.month_short_'.$month);
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
        $stats = Transaction::where('church_id', $churchId)
            ->where('direction', Transaction::DIRECTION_IN)
            ->where('status', Transaction::STATUS_COMPLETED)
            ->whereNotIn('source_type', [Transaction::SOURCE_EXCHANGE, Transaction::SOURCE_ALLOCATION])
            ->selectRaw('
                SUM(CASE WHEN date >= ? THEN COALESCE(amount_uah, amount) ELSE 0 END) as this_week_income,
                SUM(CASE WHEN date >= ? AND date <= ? THEN COALESCE(amount_uah, amount) ELSE 0 END) as last_week_income,
                SUM(CASE WHEN date >= ? THEN COALESCE(amount_uah, amount) ELSE 0 END) as this_month_total,
                COUNT(CASE WHEN date >= ? THEN 1 END) as this_month_count,
                SUM(CASE WHEN date >= ? AND date <= ? THEN COALESCE(amount_uah, amount) ELSE 0 END) as last_month_total,
                COUNT(CASE WHEN date >= ? AND date <= ? THEN 1 END) as last_month_count,
                COUNT(DISTINCT CASE WHEN date >= ? AND person_id IS NOT NULL THEN person_id END) as this_month_donors,
                COUNT(DISTINCT CASE WHEN date >= ? AND date <= ? AND person_id IS NOT NULL THEN person_id END) as last_month_donors
            ', [
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
        $totalTransactions = Transaction::where('church_id', $churchId)
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
    public function cards(Request $request)
    {
        if (! auth()->user()->canView('finances')) {
            return $this->errorResponse($request, __('messages.no_access_to_section'));
        }

        $church = $this->getCurrentChurch();

        // Check connection status for both banks
        $monobankConnected = ! empty($church->monobank_token);
        $privatbankConnected = ! empty($church->privatbank_merchant_id);

        return view('finances.cards.index', compact('church', 'monobankConnected', 'privatbankConnected'));
    }

    // ============================
    // Church Budget methods
    // ============================

    public function storeChurchBudget(Request $request)
    {
        if (! auth()->user()->canEdit('finances')) {
            abort(403);
        }

        $church = $this->getCurrentChurch();

        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2100',
            'name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $existing = ChurchBudget::where('church_id', $church->id)
            ->where('year', $validated['year'])
            ->first();

        if ($existing) {
            return $this->errorResponse($request, __('app.budget_for_year_exists'));
        }

        $budget = new ChurchBudget([
            'name' => $validated['name'] ?? (__('app.budget').' '.$validated['year']),
            'year' => $validated['year'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'active',
        ]);
        $budget->church_id = $church->id;
        $budget->save();

        broadcast(new ChurchDataUpdated($church->id, 'budgets', 'created'))->toOthers();

        return $this->successResponse($request, __('app.church_budget_created'));
    }

    public function updateChurchBudget(Request $request, ChurchBudget $churchBudget)
    {
        if (! auth()->user()->canEdit('finances')) {
            abort(403);
        }

        $church = $this->getCurrentChurch();
        if ($churchBudget->church_id !== $church->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,active,closed',
            'notes' => 'nullable|string|max:1000',
        ]);

        $updateData = [];
        if (array_key_exists('name', $validated) && $validated['name'] !== null) {
            $updateData['name'] = $validated['name'];
        }
        if (array_key_exists('status', $validated) && $validated['status'] !== null) {
            $updateData['status'] = $validated['status'];
        }
        if (array_key_exists('notes', $validated)) {
            $updateData['notes'] = $validated['notes']; // allow null to clear
        }
        $churchBudget->update($updateData);

        broadcast(new ChurchDataUpdated($church->id, 'budgets', 'updated'))->toOthers();

        return $this->successResponse($request, __('app.church_budget_updated'));
    }

    public function storeChurchBudgetItem(Request $request, ChurchBudget $churchBudget)
    {
        if (! auth()->user()->canEdit('finances')) {
            abort(403);
        }

        $church = $this->getCurrentChurch();
        if ($churchBudget->church_id !== $church->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:transaction_categories,id',
            'is_recurring' => 'required|boolean',
            'amount' => 'required_if:is_recurring,true|nullable|numeric|min:0',
            'one_time_month' => 'required_if:is_recurring,false|nullable|integer|min:1|max:12',
            'one_time_amount' => 'required_if:is_recurring,false|nullable|numeric|min:0',
            'amounts_custom' => 'nullable|array',
            'amounts_custom.*' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Build amounts JSON
        if (! empty($validated['amounts_custom'])) {
            $amounts = [];
            foreach ($validated['amounts_custom'] as $m => $val) {
                $m = (int) $m;
                if ($m >= 1 && $m <= 12 && $val > 0) {
                    $amounts[(string) $m] = (float) $val;
                }
            }
        } elseif ($validated['is_recurring']) {
            $amounts = [];
            for ($m = 1; $m <= 12; $m++) {
                $amounts[(string) $m] = (float) $validated['amount'];
            }
        } else {
            $amounts = [(string) $validated['one_time_month'] => (float) $validated['one_time_amount']];
        }

        // Verify category belongs to this church
        if (! empty($validated['category_id'])) {
            $cat = TransactionCategory::where('id', $validated['category_id'])
                ->where('church_id', $church->id)
                ->first();
            if (! $cat) {
                abort(404);
            }
        }

        $maxSort = ChurchBudgetItem::where('church_budget_id', $churchBudget->id)->max('sort_order') ?? 0;

        $budgetItem = new ChurchBudgetItem([
            'category_id' => $validated['category_id'] ?? null,
            'name' => $validated['name'],
            'is_recurring' => $validated['is_recurring'],
            'amounts' => $amounts,
            'notes' => $validated['notes'] ?? null,
            'sort_order' => $maxSort + 1,
        ]);
        $budgetItem->church_id = $church->id;
        $budgetItem->church_budget_id = $churchBudget->id;
        $budgetItem->save();

        broadcast(new ChurchDataUpdated($church->id, 'budgets', 'item_added'))->toOthers();

        return $this->successResponse($request, __('app.budget_item_added'));
    }

    public function updateChurchBudgetItem(Request $request, ChurchBudgetItem $item)
    {
        if (! auth()->user()->canEdit('finances')) {
            abort(403);
        }

        $church = $this->getCurrentChurch();
        if ($item->church_id !== $church->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:transaction_categories,id',
            'is_recurring' => 'required|boolean',
            'amount' => 'required_if:is_recurring,true|nullable|numeric|min:0',
            'one_time_month' => 'required_if:is_recurring,false|nullable|integer|min:1|max:12',
            'one_time_amount' => 'required_if:is_recurring,false|nullable|numeric|min:0',
            'amounts_custom' => 'nullable|array',
            'amounts_custom.*' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Build amounts JSON
        if (! empty($validated['amounts_custom'])) {
            // Custom per-month amounts — only allow keys 1-12
            $amounts = [];
            foreach ($validated['amounts_custom'] as $m => $val) {
                $m = (int) $m;
                if ($m >= 1 && $m <= 12 && $val > 0) {
                    $amounts[(string) $m] = (float) $val;
                }
            }
        } elseif ($validated['is_recurring']) {
            $amounts = [];
            for ($m = 1; $m <= 12; $m++) {
                $amounts[(string) $m] = (float) $validated['amount'];
            }
        } else {
            $amounts = [(string) $validated['one_time_month'] => (float) $validated['one_time_amount']];
        }

        // Verify category belongs to this church
        if (! empty($validated['category_id'])) {
            $cat = TransactionCategory::where('id', $validated['category_id'])
                ->where('church_id', $church->id)
                ->first();
            if (! $cat) {
                abort(404);
            }
        }

        $item->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'] ?? null,
            'is_recurring' => $validated['is_recurring'],
            'amounts' => $amounts,
            'notes' => $validated['notes'] ?? null,
        ]);

        broadcast(new ChurchDataUpdated($church->id, 'budgets', 'item_updated'))->toOthers();

        return $this->successResponse($request, __('app.budget_item_updated'));
    }

    public function destroyChurchBudgetItem(Request $request, ChurchBudgetItem $item)
    {
        if (! auth()->user()->canEdit('finances')) {
            abort(403);
        }

        $church = $this->getCurrentChurch();
        if ($item->church_id !== $church->id) {
            abort(404);
        }

        $item->delete();

        broadcast(new ChurchDataUpdated($church->id, 'budgets', 'item_deleted'))->toOthers();

        return $this->successResponse($request, __('app.budget_item_deleted'));
    }

    public function churchBudgetItemTransactions(Request $request, ChurchBudgetItem $item)
    {
        if (! auth()->user()->canView('finances')) {
            abort(403);
        }

        $church = $this->getCurrentChurch();
        if ($item->church_id !== $church->id) {
            abort(404);
        }

        $month = max(1, min(12, (int) $request->get('month', now()->month)));
        $year = (int) $request->get('year', $item->churchBudget->year);
        $transactions = $item->getMatchedTransactions($month, $year);

        return response()->json([
            'transactions' => $transactions->map(fn ($t) => [
                'id' => $t->id,
                'date' => $t->date?->format('d.m.Y'),
                'description' => $t->description,
                'amount' => (float) ($t->amount_uah ?? $t->amount),
                'currency' => $t->currency,
                'original_amount' => (float) $t->amount,
                'payment_method' => $t->payment_method,
                'category' => $t->category?->name,
                'has_receipt' => $t->attachments->isNotEmpty(),
            ]),
        ]);
    }
}
