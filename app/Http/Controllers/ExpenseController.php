<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Ministry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $church = $this->getCurrentChurch();
        $user = auth()->user();

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $query = Expense::where('church_id', $church->id)
            ->forMonth($year, $month)
            ->with(['ministry', 'category', 'user']);

        // Leaders can only see their ministry expenses
        if ($user->isLeader() && $user->person) {
            $ministryIds = $user->person->leadingMinistries()->pluck('id');
            $query->whereIn('ministry_id', $ministryIds);
        }

        if ($ministryId = $request->get('ministry')) {
            $query->where('ministry_id', $ministryId);
        }

        $expenses = $query->orderByDesc('date')->paginate(20);

        // Get accessible ministries
        $ministriesQuery = Ministry::where('church_id', $church->id);
        if ($user->isLeader() && $user->person) {
            $ministriesQuery->where('leader_id', $user->person->id);
        }
        $ministries = $ministriesQuery->get();

        // Calculate totals
        $totals = [
            'budget' => $ministries->sum('monthly_budget'),
            'spent' => $ministries->sum('spent_this_month'),
        ];

        $categories = ExpenseCategory::where('church_id', $church->id)->get();

        return view('expenses.index', compact('expenses', 'ministries', 'categories', 'year', 'month', 'totals'));
    }

    public function create()
    {
        $church = $this->getCurrentChurch();
        $user = auth()->user();

        $ministriesQuery = Ministry::where('church_id', $church->id);
        if ($user->isLeader() && $user->person) {
            $ministriesQuery->where('leader_id', $user->person->id);
        }
        $ministries = $ministriesQuery->get();

        $categories = ExpenseCategory::where('church_id', $church->id)->get();

        return view('expenses.create', compact('ministries', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ministry_id' => 'required|exists:ministries,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'category_id' => 'nullable|exists:expense_categories,id',
            'date' => 'required|date',
            'receipt_photo' => 'nullable|image|max:5120',
            'notes' => 'nullable|string',
        ]);

        $church = $this->getCurrentChurch();
        $ministry = Ministry::findOrFail($validated['ministry_id']);

        // Check access
        if ($ministry->church_id !== $church->id) {
            abort(404);
        }

        Gate::authorize('manage-ministry', $ministry);

        // Handle receipt upload
        if ($request->hasFile('receipt_photo')) {
            $validated['receipt_photo'] = $request->file('receipt_photo')->store('receipts', 'public');
        }

        $validated['church_id'] = $church->id;
        $validated['user_id'] = auth()->id();

        Expense::create($validated);

        return redirect()->route('expenses.index')
            ->with('success', 'Витрату додано.');
    }

    public function edit(Expense $expense)
    {
        $this->authorizeChurch($expense);
        Gate::authorize('manage-ministry', $expense->ministry);

        $church = $this->getCurrentChurch();
        $ministries = Ministry::where('church_id', $church->id)->get();
        $categories = ExpenseCategory::where('church_id', $church->id)->get();

        return view('expenses.edit', compact('expense', 'ministries', 'categories'));
    }

    public function update(Request $request, Expense $expense)
    {
        $this->authorizeChurch($expense);
        Gate::authorize('manage-ministry', $expense->ministry);

        $validated = $request->validate([
            'ministry_id' => 'required|exists:ministries,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'category_id' => 'nullable|exists:expense_categories,id',
            'date' => 'required|date',
            'receipt_photo' => 'nullable|image|max:5120',
            'notes' => 'nullable|string',
        ]);

        // Handle receipt upload
        if ($request->hasFile('receipt_photo')) {
            if ($expense->receipt_photo) {
                Storage::disk('public')->delete($expense->receipt_photo);
            }
            $validated['receipt_photo'] = $request->file('receipt_photo')->store('receipts', 'public');
        }

        $expense->update($validated);

        return redirect()->route('expenses.index')
            ->with('success', 'Витрату оновлено.');
    }

    public function destroy(Expense $expense)
    {
        $this->authorizeChurch($expense);
        Gate::authorize('manage-ministry', $expense->ministry);

        if ($expense->receipt_photo) {
            Storage::disk('public')->delete($expense->receipt_photo);
        }

        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Витрату видалено.');
    }

    public function report(Request $request)
    {
        $church = $this->getCurrentChurch();

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        // By ministry
        $byMinistry = Ministry::where('church_id', $church->id)
            ->with(['expenses' => fn($q) => $q->forMonth($year, $month)])
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'icon' => $m->icon,
                'budget' => $m->monthly_budget,
                'spent' => $m->spent_this_month,
                'percentage' => $m->budget_usage_percent,
            ]);

        // By category
        $byCategory = ExpenseCategory::where('church_id', $church->id)
            ->withCount(['expenses as total' => function ($q) use ($year, $month) {
                $q->forMonth($year, $month)->select(\DB::raw('SUM(amount)'));
            }])
            ->get();

        // Total
        $totalSpent = Expense::where('church_id', $church->id)
            ->forMonth($year, $month)
            ->sum('amount');

        $totalBudget = Ministry::where('church_id', $church->id)
            ->sum('monthly_budget');

        // Recent expenses
        $recentExpenses = Expense::where('church_id', $church->id)
            ->forMonth($year, $month)
            ->with(['ministry', 'category', 'user'])
            ->orderByDesc('date')
            ->limit(10)
            ->get();

        return view('expenses.report', compact(
            'byMinistry',
            'byCategory',
            'totalSpent',
            'totalBudget',
            'recentExpenses',
            'year',
            'month'
        ));
    }

    public function export(Request $request)
    {
        // TODO: Implement Excel export
        return back()->with('info', 'Експорт буде доступний найближчим часом.');
    }

    private function authorizeChurch(Expense $expense): void
    {
        if ($expense->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
