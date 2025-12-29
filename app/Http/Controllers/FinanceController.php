<?php

namespace App\Http\Controllers;

use App\Models\Ministry;
use App\Models\Person;
use App\Models\Transaction;
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

        $totalIncome = $incomeQuery->sum('amount');
        $totalExpense = $expenseQuery->sum('amount');
        $balance = $totalIncome - $totalExpense;

        // Monthly data for chart
        $monthlyData = $this->getMonthlyData($church->id, $year);

        // Income by category
        $incomeByCategory = TransactionCategory::where('church_id', $church->id)
            ->forIncome()
            ->get()
            ->map(function ($cat) use ($year, $month) {
                $query = $cat->transactions()->incoming()->completed();
                if ($month) {
                    $query->forMonth($year, $month);
                } else {
                    $query->forYear($year);
                }
                $cat->total_amount = $query->sum('amount');
                return $cat;
            })
            ->sortByDesc('total_amount')
            ->filter(fn($c) => $c->total_amount > 0);

        // Expense by category
        $expenseByCategory = TransactionCategory::where('church_id', $church->id)
            ->forExpense()
            ->get()
            ->map(function ($cat) use ($year, $month) {
                $query = $cat->transactions()->outgoing()->completed();
                if ($month) {
                    $query->forMonth($year, $month);
                } else {
                    $query->forYear($year);
                }
                $cat->total_amount = $query->sum('amount');
                return $cat;
            })
            ->sortByDesc('total_amount')
            ->filter(fn($c) => $c->total_amount > 0);

        // Expense by ministry
        $expenseByMinistry = Ministry::where('church_id', $church->id)
            ->get()
            ->map(function ($ministry) use ($year, $month, $church) {
                $query = Transaction::where('church_id', $church->id)
                    ->where('ministry_id', $ministry->id)
                    ->outgoing()
                    ->completed();
                if ($month) {
                    $query->forMonth($year, $month);
                } else {
                    $query->forYear($year);
                }
                $ministry->total_expense = $query->sum('amount');
                return $ministry;
            })
            ->sortByDesc('total_expense')
            ->filter(fn($m) => $m->total_expense > 0);

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

        // Top donors (non-anonymous)
        $topDonors = Transaction::where('church_id', $church->id)
            ->incoming()
            ->completed()
            ->where('is_anonymous', false)
            ->whereNotNull('person_id')
            ->when($month, fn($q) => $q->forMonth($year, $month), fn($q) => $q->forYear($year))
            ->select('person_id', DB::raw('SUM(amount) as total'))
            ->groupBy('person_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('person')
            ->get();

        return view('finances.index', compact(
            'year', 'month', 'periodLabel',
            'totalIncome', 'totalExpense', 'balance',
            'monthlyData',
            'incomeByCategory', 'expenseByCategory', 'expenseByMinistry',
            'recentIncomes', 'recentExpenses',
            'yearComparison', 'topDonors'
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

        return view('finances.incomes.create', compact('categories', 'people'));
    }

    public function storeIncome(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:transaction_categories,id', new BelongsToChurch(TransactionCategory::class, 'income')],
            'amount' => 'required|numeric|min:0.01',
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

        return view('finances.incomes.edit', compact('income', 'categories', 'people'));
    }

    public function updateIncome(Request $request, Transaction $income)
    {
        $this->authorizeChurch($income);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:transaction_categories,id', new BelongsToChurch(TransactionCategory::class, 'income')],
            'amount' => 'required|numeric|min:0.01',
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

    public function createExpense()
    {
        $church = $this->getCurrentChurch();
        $categories = TransactionCategory::where('church_id', $church->id)
            ->forExpense()
            ->orderBy('sort_order')
            ->get();
        $ministries = Ministry::where('church_id', $church->id)->orderBy('name')->get();

        return view('finances.expenses.create', compact('categories', 'ministries'));
    }

    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:transaction_categories,id', new BelongsToChurch(TransactionCategory::class, 'expense')],
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'ministry_id' => ['nullable', 'exists:ministries,id', new BelongsToChurch(Ministry::class)],
            'description' => 'required|string|max:255',
            'payment_method' => 'nullable|in:cash,card,transfer,online',
            'notes' => 'nullable|string',
            'force_over_budget' => 'boolean',
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

        Transaction::create([
            'church_id' => $church->id,
            'direction' => Transaction::DIRECTION_OUT,
            'source_type' => Transaction::SOURCE_EXPENSE,
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'category_id' => $validated['category_id'] ?? null,
            'ministry_id' => $validated['ministry_id'] ?? null,
            'payment_method' => $validated['payment_method'] ?? null,
            'description' => $validated['description'],
            'notes' => $validated['notes'] ?? null,
            'status' => Transaction::STATUS_COMPLETED,
            'recorded_by' => auth()->id(),
        ]);

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

        return view('finances.expenses.edit', compact('expense', 'categories', 'ministries'));
    }

    public function updateExpense(Request $request, Transaction $expense)
    {
        $this->authorizeChurch($expense);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:transaction_categories,id', new BelongsToChurch(TransactionCategory::class, 'expense')],
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'ministry_id' => ['nullable', 'exists:ministries,id', new BelongsToChurch(Ministry::class)],
            'description' => 'required|string|max:255',
            'payment_method' => 'nullable|in:cash,card,transfer,online',
            'notes' => 'nullable|string',
            'force_over_budget' => 'boolean',
        ]);

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
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $income = Transaction::where('church_id', $churchId)
                ->incoming()
                ->completed()
                ->forMonth($year, $m)
                ->sum('amount');

            $expense = Transaction::where('church_id', $churchId)
                ->outgoing()
                ->completed()
                ->forMonth($year, $m)
                ->sum('amount');

            $months[] = [
                'month' => $this->getMonthName($m),
                'income' => (float) $income,
                'expense' => (float) $expense,
                'balance' => (float) ($income - $expense),
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

    protected function authorizeChurch($model): void
    {
        if ($model->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
