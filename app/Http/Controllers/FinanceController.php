<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\Ministry;
use App\Models\Person;
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

        // Calculate totals
        $incomeQuery = Income::where('church_id', $church->id);
        $expenseQuery = Expense::where('church_id', $church->id);

        if ($month) {
            $incomeQuery->forMonth($year, $month);
            $expenseQuery->forMonth($year, $month);
            $periodLabel = $this->getMonthName($month) . ' ' . $year;
        } else {
            $incomeQuery->forYear($year);
            $expenseQuery->whereYear('date', $year);
            $periodLabel = $year . ' рік';
        }

        $totalIncome = $incomeQuery->sum('amount');
        $totalExpense = $expenseQuery->sum('amount');
        $balance = $totalIncome - $totalExpense;

        // Monthly data for chart (last 12 months or selected year)
        $monthlyData = $this->getMonthlyData($church->id, $year);

        // Income by category
        $incomeByCategory = IncomeCategory::where('church_id', $church->id)
            ->withSum(['incomes' => function($q) use ($year, $month) {
                $month ? $q->forMonth($year, $month) : $q->forYear($year);
            }], 'amount')
            ->orderByDesc('incomes_sum_amount')
            ->get();

        // Expense by category
        $expenseByCategory = ExpenseCategory::where('church_id', $church->id)
            ->withSum(['expenses' => function($q) use ($year, $month) {
                $month ? $q->forMonth($year, $month) : $q->whereYear('date', $year);
            }], 'amount')
            ->orderByDesc('expenses_sum_amount')
            ->get();

        // Expense by ministry
        $expenseByMinistry = Ministry::where('church_id', $church->id)
            ->withSum(['expenses' => function($q) use ($year, $month) {
                $month ? $q->forMonth($year, $month) : $q->whereYear('date', $year);
            }], 'amount')
            ->having('expenses_sum_amount', '>', 0)
            ->orderByDesc('expenses_sum_amount')
            ->get();

        // Recent transactions
        $recentIncomes = Income::where('church_id', $church->id)
            ->with(['category', 'person'])
            ->orderByDesc('date')
            ->limit(5)
            ->get();

        $recentExpenses = Expense::where('church_id', $church->id)
            ->with(['category', 'ministry'])
            ->orderByDesc('date')
            ->limit(5)
            ->get();

        // Year comparison
        $yearComparison = $this->getYearComparison($church->id, $year);

        // Top donors (non-anonymous)
        $topDonors = Income::where('church_id', $church->id)
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

    // Income CRUD
    public function incomes(Request $request)
    {
        $church = $this->getCurrentChurch();

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $query = Income::where('church_id', $church->id)
            ->forMonth($year, $month)
            ->with(['category', 'person', 'user']);

        if ($categoryId = $request->get('category')) {
            $query->where('category_id', $categoryId);
        }

        $incomes = $query->orderByDesc('date')->paginate(20);

        $categories = IncomeCategory::where('church_id', $church->id)->orderBy('sort_order')->get();

        $totals = [
            'total' => Income::where('church_id', $church->id)->forMonth($year, $month)->sum('amount'),
            'tithes' => Income::where('church_id', $church->id)->forMonth($year, $month)->tithes()->sum('amount'),
            'offerings' => Income::where('church_id', $church->id)->forMonth($year, $month)->offerings()->sum('amount'),
        ];

        return view('finances.incomes.index', compact('incomes', 'categories', 'year', 'month', 'totals'));
    }

    public function createIncome()
    {
        $church = $this->getCurrentChurch();
        $categories = IncomeCategory::where('church_id', $church->id)->orderBy('sort_order')->get();
        $people = Person::where('church_id', $church->id)->orderBy('first_name')->get();

        return view('finances.incomes.create', compact('categories', 'people'));
    }

    public function storeIncome(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:income_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'person_id' => 'nullable|exists:people,id',
            'description' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,card,transfer,online',
            'is_anonymous' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $church = $this->getCurrentChurch();

        $validated['church_id'] = $church->id;
        $validated['user_id'] = auth()->id();
        $validated['is_anonymous'] = $request->boolean('is_anonymous');

        if ($validated['is_anonymous']) {
            $validated['person_id'] = null;
        }

        Income::create($validated);

        return redirect()->route('finances.incomes')
            ->with('success', 'Надходження додано.');
    }

    public function editIncome(Income $income)
    {
        $this->authorizeChurch($income);

        $church = $this->getCurrentChurch();
        $categories = IncomeCategory::where('church_id', $church->id)->orderBy('sort_order')->get();
        $people = Person::where('church_id', $church->id)->orderBy('first_name')->get();

        return view('finances.incomes.edit', compact('income', 'categories', 'people'));
    }

    public function updateIncome(Request $request, Income $income)
    {
        $this->authorizeChurch($income);

        $validated = $request->validate([
            'category_id' => 'required|exists:income_categories,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'person_id' => 'nullable|exists:people,id',
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

    public function destroyIncome(Income $income)
    {
        $this->authorizeChurch($income);
        $income->delete();

        return redirect()->route('finances.incomes')
            ->with('success', 'Надходження видалено.');
    }

    // Income Categories
    public function incomeCategories()
    {
        $church = $this->getCurrentChurch();
        $categories = IncomeCategory::where('church_id', $church->id)
            ->orderBy('sort_order')
            ->withCount('incomes')
            ->get();

        return view('finances.income-categories.index', compact('categories'));
    }

    public function storeIncomeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
            'is_tithe' => 'boolean',
            'is_offering' => 'boolean',
            'is_donation' => 'boolean',
        ]);

        $church = $this->getCurrentChurch();

        $validated['church_id'] = $church->id;
        $validated['sort_order'] = IncomeCategory::where('church_id', $church->id)->max('sort_order') + 1;

        IncomeCategory::create($validated);

        return back()->with('success', 'Категорію додано.');
    }

    public function updateIncomeCategory(Request $request, IncomeCategory $incomeCategory)
    {
        if ($incomeCategory->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
            'is_tithe' => 'boolean',
            'is_offering' => 'boolean',
            'is_donation' => 'boolean',
        ]);

        $incomeCategory->update($validated);

        return back()->with('success', 'Категорію оновлено.');
    }

    public function destroyIncomeCategory(IncomeCategory $incomeCategory)
    {
        if ($incomeCategory->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }

        if ($incomeCategory->incomes()->count() > 0) {
            return back()->with('error', 'Неможливо видалити категорію з надходженнями.');
        }

        $incomeCategory->delete();

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
            $income = Income::where('church_id', $churchId)
                ->forMonth($year, $m)
                ->sum('amount');

            $expense = Expense::where('church_id', $churchId)
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
            'income' => Income::where('church_id', $churchId)->forYear($year)->sum('amount'),
            'expense' => Expense::where('church_id', $churchId)->whereYear('date', $year)->sum('amount'),
        ];
        $currentYear['balance'] = $currentYear['income'] - $currentYear['expense'];

        $prevYear = [
            'income' => Income::where('church_id', $churchId)->forYear($year - 1)->sum('amount'),
            'expense' => Expense::where('church_id', $churchId)->whereYear('date', $year - 1)->sum('amount'),
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

    private function authorizeChurch($model): void
    {
        if ($model->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
