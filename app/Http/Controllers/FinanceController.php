<?php

namespace App\Http\Controllers;

use App\Helpers\CurrencyHelper;
use App\Models\DonationCampaign;
use App\Models\ExchangeRate;
use App\Models\Ministry;
use App\Models\Person;
use App\Models\Transaction;
use App\Models\TransactionAttachment;
use App\Models\TransactionCategory;
use App\Rules\BelongsToChurch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $church = $this->getCurrentChurch();

        $year = $request->get('year', now()->year);
        $month = $request->get('month');

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

        // Calculate totals in UAH (using amount_uah for converted amounts)
        $hasAmountUah = \Schema::hasColumn('transactions', 'amount_uah');
        $totalIncome = $hasAmountUah ? ($incomeQuery->sum('amount_uah') ?: $incomeQuery->sum('amount')) : $incomeQuery->sum('amount');
        $totalExpense = $hasAmountUah ? ($expenseQuery->sum('amount_uah') ?: $expenseQuery->sum('amount')) : $expenseQuery->sum('amount');
        $periodBalance = $totalIncome - $totalExpense;

        // Overall balance (includes initial balance) - all in UAH
        $initialBalance = (float) $church->initial_balance;
        $initialBalanceDate = $church->initial_balance_date;
        $allTimeIncome = $hasAmountUah
            ? (Transaction::where('church_id', $church->id)->incoming()->completed()->sum('amount_uah') ?: Transaction::where('church_id', $church->id)->incoming()->completed()->sum('amount'))
            : Transaction::where('church_id', $church->id)->incoming()->completed()->sum('amount');
        $allTimeExpense = $hasAmountUah
            ? (Transaction::where('church_id', $church->id)->outgoing()->completed()->sum('amount_uah') ?: Transaction::where('church_id', $church->id)->outgoing()->completed()->sum('amount'))
            : Transaction::where('church_id', $church->id)->outgoing()->completed()->sum('amount');
        $currentBalance = $initialBalance + $allTimeIncome - $allTimeExpense;

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
            ->selectRaw('transaction_categories.*, COALESCE(SUM(transactions.amount), 0) as total_amount')
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
            ->selectRaw('transaction_categories.*, COALESCE(SUM(transactions.amount), 0) as total_amount')
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
            ->selectRaw('ministries.*, COALESCE(SUM(transactions.amount), 0) as total_expense')
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

        return view('finances.index', compact(
            'church', 'year', 'month', 'periodLabel',
            'totalIncome', 'totalExpense', 'periodBalance',
            'initialBalance', 'initialBalanceDate', 'currentBalance',
            'allTimeIncome', 'allTimeExpense',
            'incomeByCurrency', 'expenseByCurrency',
            'exchangeRates', 'enabledCurrencies',
            'monthlyData',
            'incomeByCategory', 'expenseByCategory', 'expenseByMinistry',
            'recentIncomes', 'recentExpenses',
            'yearComparison',
            'quickStats', 'activeCampaigns', 'paymentMethods', 'activityFeed'
        ));
    }

    // Income/Transactions list
    public function incomes(Request $request)
    {
        $church = $this->getCurrentChurch();

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $query = Transaction::where('church_id', $church->id)
            ->incoming()
            ->forMonth($year, $month)
            ->with(['category', 'person', 'recorder']);

        if ($categoryId = $request->get('category')) {
            $query->where('category_id', $categoryId);
        }

        $incomes = $query->orderByDesc('date')->paginate(20);

        $categories = TransactionCategory::where('church_id', $church->id)
            ->forIncome()
            ->orderBy('sort_order')
            ->get();

        $totals = [
            'total' => Transaction::where('church_id', $church->id)->incoming()->completed()->forMonth($year, $month)->sum('amount'),
            'tithes' => Transaction::where('church_id', $church->id)->incoming()->completed()->forMonth($year, $month)->tithes()->sum('amount'),
            'offerings' => Transaction::where('church_id', $church->id)->incoming()->completed()->forMonth($year, $month)->offerings()->sum('amount'),
        ];

        return view('finances.incomes.index', compact('incomes', 'categories', 'year', 'month', 'totals'));
    }

    public function createIncome()
    {
        $church = $this->getCurrentChurch();
        $categories = TransactionCategory::where('church_id', $church->id)
            ->forIncome()
            ->orderBy('sort_order')
            ->get();
        $people = Person::where('church_id', $church->id)->orderBy('first_name')->get();
        $enabledCurrencies = CurrencyHelper::getEnabledCurrencies($church->enabled_currencies);
        $exchangeRates = ExchangeRate::getLatestRates();

        return view('finances.incomes.create', compact('categories', 'people', 'enabledCurrencies', 'exchangeRates'));
    }

    public function storeIncome(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:transaction_categories,id', new BelongsToChurch(TransactionCategory::class, 'income')],
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|in:UAH,USD,EUR',
            'date' => 'required|date',
            'person_id' => ['nullable', 'exists:people,id', new BelongsToChurch(Person::class)],
            'description' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,card,transfer,online',
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

        Transaction::create([
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

        return redirect()->route('finances.incomes')
            ->with('success', 'Надходження додано.');
    }

    public function editIncome(Transaction $income)
    {
        $this->authorizeChurch($income);

        $church = $this->getCurrentChurch();
        $categories = TransactionCategory::where('church_id', $church->id)
            ->forIncome()
            ->orderBy('sort_order')
            ->get();
        $people = Person::where('church_id', $church->id)->orderBy('first_name')->get();
        $enabledCurrencies = CurrencyHelper::getEnabledCurrencies($church->enabled_currencies);
        $exchangeRates = ExchangeRate::getLatestRates();

        return view('finances.incomes.edit', compact('income', 'categories', 'people', 'enabledCurrencies', 'exchangeRates'));
    }

    public function updateIncome(Request $request, Transaction $income)
    {
        $this->authorizeChurch($income);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:transaction_categories,id', new BelongsToChurch(TransactionCategory::class, 'income')],
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|in:UAH,USD,EUR',
            'date' => 'required|date',
            'person_id' => ['nullable', 'exists:people,id', new BelongsToChurch(Person::class)],
            'description' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,card,transfer,online',
            'is_anonymous' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['is_anonymous'] = $request->boolean('is_anonymous');
        if ($validated['is_anonymous']) {
            $validated['person_id'] = null;
        }
        $validated['currency'] = $validated['currency'] ?? 'UAH';

        $income->update($validated);

        return redirect()->route('finances.incomes')
            ->with('success', 'Надходження оновлено.');
    }

    public function destroyIncome(Transaction $income)
    {
        $this->authorizeChurch($income);
        $income->delete();

        return back()->with('success', 'Надходження видалено.');
    }

    // Expenses
    public function expenses(Request $request)
    {
        $church = $this->getCurrentChurch();

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $query = Transaction::where('church_id', $church->id)
            ->outgoing()
            ->forMonth($year, $month)
            ->with(['category', 'ministry', 'recorder']);

        if ($categoryId = $request->get('category')) {
            $query->where('category_id', $categoryId);
        }

        $expenses = $query->orderByDesc('date')->paginate(20);

        $categories = TransactionCategory::where('church_id', $church->id)
            ->forExpense()
            ->orderBy('sort_order')
            ->get();

        $spent = Transaction::where('church_id', $church->id)
            ->outgoing()
            ->completed()
            ->forMonth($year, $month)
            ->sum('amount');

        $ministries = Ministry::where('church_id', $church->id)->orderBy('name')->get();

        $budget = $ministries->whereNotNull('monthly_budget')->sum('monthly_budget');

        $totals = [
            'budget' => $budget,
            'spent' => $spent,
        ];

        return view('finances.expenses.index', compact('expenses', 'categories', 'year', 'month', 'totals', 'ministries'));
    }

    public function createExpense(Request $request)
    {
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
        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:transaction_categories,id', new BelongsToChurch(TransactionCategory::class, 'expense')],
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|in:UAH,USD,EUR',
            'date' => 'required|date',
            'ministry_id' => ['nullable', 'exists:ministries,id', new BelongsToChurch(Ministry::class)],
            'description' => 'required|string|max:255',
            'payment_method' => 'nullable|in:cash,card,transfer,online',
            'notes' => 'nullable|string',
            'force_over_budget' => 'boolean',
            'receipts' => 'nullable|array|max:10',
            'receipts.*' => 'file|mimes:jpg,jpeg,png,gif,webp,pdf|max:10240',
        ]);

        $church = $this->getCurrentChurch();

        // Check ministry budget limits
        $budgetWarning = null;
        if (!empty($validated['ministry_id'])) {
            $ministry = Ministry::find($validated['ministry_id']);
            if ($ministry) {
                $budgetCheck = $ministry->canAddExpense((float) $validated['amount']);

                if (!$budgetCheck['allowed'] && !$request->boolean('force_over_budget')) {
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
                $path = $file->store("receipts/{$church->id}", 'public');

                $transaction->attachments()->create([
                    'filename' => basename($path),
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        $message = 'Витрату додано.';
        if ($budgetWarning) {
            $message .= ' ' . $budgetWarning;
        }

        return redirect()->route('finances.expenses.index')
            ->with('success', $message)
            ->with('budget_warning', $budgetWarning);
    }

    public function editExpense(Transaction $expense)
    {
        $this->authorizeChurch($expense);

        $church = $this->getCurrentChurch();
        $categories = TransactionCategory::where('church_id', $church->id)
            ->forExpense()
            ->orderBy('sort_order')
            ->get();
        $ministries = Ministry::where('church_id', $church->id)->orderBy('name')->get();
        $enabledCurrencies = CurrencyHelper::getEnabledCurrencies($church->enabled_currencies);
        $exchangeRates = ExchangeRate::getLatestRates();

        $expense->load('attachments');

        return view('finances.expenses.edit', compact('expense', 'categories', 'ministries', 'enabledCurrencies', 'exchangeRates'));
    }

    public function updateExpense(Request $request, Transaction $expense)
    {
        $this->authorizeChurch($expense);

        $validated = $request->validate([
            'category_id' => ['nullable', 'exists:transaction_categories,id', new BelongsToChurch(TransactionCategory::class, 'expense')],
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|in:UAH,USD,EUR',
            'date' => 'required|date',
            'ministry_id' => ['nullable', 'exists:ministries,id', new BelongsToChurch(Ministry::class)],
            'description' => 'required|string|max:255',
            'payment_method' => 'nullable|in:cash,card,transfer,online',
            'notes' => 'nullable|string',
            'force_over_budget' => 'boolean',
            'receipts' => 'nullable|array|max:10',
            'receipts.*' => 'file|mimes:jpg,jpeg,png,gif,webp,pdf|max:10240',
            'delete_attachments' => 'nullable|array',
            'delete_attachments.*' => 'integer|exists:transaction_attachments,id',
        ]);

        $validated['currency'] = $validated['currency'] ?? 'UAH';

        // Check ministry budget limits (only if amount increased or ministry changed)
        $budgetWarning = null;
        $newMinistryId = $validated['ministry_id'] ?? null;
        $amountDifference = (float) $validated['amount'] - (float) $expense->amount;

        // Check budget if: ministry changed to new one, or amount increased for same ministry
        if ($newMinistryId) {
            $ministry = Ministry::find($newMinistryId);
            if ($ministry) {
                // Calculate effective new expense for budget check
                $checkAmount = ($expense->ministry_id === $newMinistryId)
                    ? $amountDifference  // Same ministry - only check the increase
                    : (float) $validated['amount'];  // New ministry - check full amount

                if ($checkAmount > 0) {
                    $budgetCheck = $ministry->canAddExpense($checkAmount);

                    if (!$budgetCheck['allowed'] && !$request->boolean('force_over_budget')) {
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

        $expense->update($validated);

        // Delete marked attachments
        if (!empty($validated['delete_attachments'])) {
            $expense->attachments()
                ->whereIn('id', $validated['delete_attachments'])
                ->get()
                ->each(fn ($att) => $att->delete());
        }

        // Handle new receipt uploads
        $church = $this->getCurrentChurch();
        if ($request->hasFile('receipts')) {
            foreach ($request->file('receipts') as $file) {
                $path = $file->store("receipts/{$church->id}", 'public');

                $expense->attachments()->create([
                    'filename' => basename($path),
                    'original_name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        $message = 'Витрату оновлено.';
        if ($budgetWarning) {
            $message .= ' ' . $budgetWarning;
        }

        return redirect()->route('finances.expenses.index')
            ->with('success', $message);
    }

    public function destroyExpense(Transaction $expense)
    {
        $this->authorizeChurch($expense);
        $expense->delete();

        return back()->with('success', 'Витрату видалено.');
    }

    // Categories (unified)
    public function categories()
    {
        $church = $this->getCurrentChurch();
        $incomeCategories = TransactionCategory::where('church_id', $church->id)
            ->forIncome()
            ->orderBy('sort_order')
            ->withCount('transactions')
            ->get();

        $expenseCategories = TransactionCategory::where('church_id', $church->id)
            ->forExpense()
            ->orderBy('sort_order')
            ->withCount('transactions')
            ->get();

        return view('finances.categories.index', compact('incomeCategories', 'expenseCategories'));
    }

    public function storeCategory(Request $request)
    {
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
        if ($category->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }

        if ($category->transactions()->count() > 0) {
            return back()->with('error', 'Неможливо видалити категорію з транзакціями.');
        }

        $category->delete();

        return back()->with('success', 'Категорію видалено.');
    }

    // Analytics API
    public function chartData(Request $request)
    {
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

    private function getQuickStats(int $churchId): array
    {
        $now = Carbon::now();
        $thisWeekStart = $now->copy()->startOfWeek();
        $lastWeekStart = $now->copy()->subWeek()->startOfWeek();
        $lastWeekEnd = $now->copy()->subWeek()->endOfWeek();
        $thisMonthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // This week income
        $thisWeekIncome = Transaction::where('church_id', $churchId)
            ->incoming()->completed()
            ->where('date', '>=', $thisWeekStart)
            ->sum('amount');

        // Last week income
        $lastWeekIncome = Transaction::where('church_id', $churchId)
            ->incoming()->completed()
            ->whereBetween('date', [$lastWeekStart, $lastWeekEnd])
            ->sum('amount');

        // Week change percentage
        $weekChange = $lastWeekIncome > 0
            ? round((($thisWeekIncome - $lastWeekIncome) / $lastWeekIncome) * 100, 0)
            : ($thisWeekIncome > 0 ? 100 : 0);

        // This month donors count
        $thisMonthDonors = Transaction::where('church_id', $churchId)
            ->incoming()->completed()
            ->where('date', '>=', $thisMonthStart)
            ->whereNotNull('person_id')
            ->distinct('person_id')
            ->count('person_id');

        // Last month donors count
        $lastMonthDonors = Transaction::where('church_id', $churchId)
            ->incoming()->completed()
            ->whereBetween('date', [$lastMonthStart, $lastMonthEnd])
            ->whereNotNull('person_id')
            ->distinct('person_id')
            ->count('person_id');

        // Donors change
        $donorsChange = $thisMonthDonors - $lastMonthDonors;

        // Average donation this month
        $thisMonthTotal = Transaction::where('church_id', $churchId)
            ->incoming()->completed()
            ->where('date', '>=', $thisMonthStart)
            ->sum('amount');

        $thisMonthCount = Transaction::where('church_id', $churchId)
            ->incoming()->completed()
            ->where('date', '>=', $thisMonthStart)
            ->count();

        $avgDonation = $thisMonthCount > 0 ? round($thisMonthTotal / $thisMonthCount, 0) : 0;

        // Last month average
        $lastMonthTotal = Transaction::where('church_id', $churchId)
            ->incoming()->completed()
            ->whereBetween('date', [$lastMonthStart, $lastMonthEnd])
            ->sum('amount');

        $lastMonthCount = Transaction::where('church_id', $churchId)
            ->incoming()->completed()
            ->whereBetween('date', [$lastMonthStart, $lastMonthEnd])
            ->count();

        $lastMonthAvg = $lastMonthCount > 0 ? round($lastMonthTotal / $lastMonthCount, 0) : 0;
        $avgChange = $lastMonthAvg > 0
            ? round((($avgDonation - $lastMonthAvg) / $lastMonthAvg) * 100, 0)
            : ($avgDonation > 0 ? 100 : 0);

        // Total transactions this month
        $totalTransactions = Transaction::where('church_id', $churchId)
            ->completed()
            ->where('date', '>=', $thisMonthStart)
            ->count();

        return [
            'thisWeekIncome' => $thisWeekIncome,
            'lastWeekIncome' => $lastWeekIncome,
            'weekChange' => $weekChange,
            'thisMonthDonors' => $thisMonthDonors,
            'lastMonthDonors' => $lastMonthDonors,
            'donorsChange' => $donorsChange,
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
        $church = $this->getCurrentChurch();

        // Check connection status for both banks
        $monobankConnected = !empty($church->monobank_token);
        $privatbankConnected = !empty($church->privatbank_merchant_id);

        return view('finances.cards.index', compact('church', 'monobankConnected', 'privatbankConnected'));
    }
}
