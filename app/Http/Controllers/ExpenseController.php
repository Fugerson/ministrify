<?php

namespace App\Http\Controllers;

use App\Models\Ministry;
use App\Models\Transaction;
use App\Models\TransactionCategory;
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

        $query = Transaction::where('church_id', $church->id)
            ->where('direction', Transaction::DIRECTION_OUT)
            ->forMonth($year, $month)
            ->with(['ministry', 'category', 'recorder']);

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
            'spent' => $this->calculateSpentThisMonth($church->id, $ministries->pluck('id')->toArray()),
        ];

        $categories = TransactionCategory::where('church_id', $church->id)
            ->where('type', 'expense')
            ->get();

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

        $categories = TransactionCategory::where('church_id', $church->id)
            ->where('type', 'expense')
            ->get();

        return view('expenses.create', compact('ministries', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ministry_id' => 'required|exists:ministries,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'category_id' => 'nullable|exists:transaction_categories,id',
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
        $receiptPath = null;
        if ($request->hasFile('receipt_photo')) {
            $receiptPath = $request->file('receipt_photo')->store('receipts', 'public');
        }

        Transaction::create([
            'church_id' => $church->id,
            'direction' => Transaction::DIRECTION_OUT,
            'source_type' => Transaction::SOURCE_EXPENSE,
            'ministry_id' => $validated['ministry_id'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
            'date' => $validated['date'],
            'notes' => $validated['notes'],
            'payment_data' => $receiptPath ? ['receipt_photo' => $receiptPath] : null,
            'recorded_by' => auth()->id(),
            'status' => Transaction::STATUS_COMPLETED,
        ]);

        return redirect()->route('finances.expenses.index')
            ->with('success', 'Витрату додано.');
    }

    public function edit(Transaction $expense)
    {
        $this->authorizeExpense($expense);
        if ($expense->ministry) {
            Gate::authorize('manage-ministry', $expense->ministry);
        }

        $church = $this->getCurrentChurch();
        $ministries = Ministry::where('church_id', $church->id)->get();
        $categories = TransactionCategory::where('church_id', $church->id)
            ->where('type', 'expense')
            ->get();

        return view('expenses.edit', compact('expense', 'ministries', 'categories'));
    }

    public function update(Request $request, Transaction $expense)
    {
        $this->authorizeExpense($expense);
        if ($expense->ministry) {
            Gate::authorize('manage-ministry', $expense->ministry);
        }

        $validated = $request->validate([
            'ministry_id' => 'required|exists:ministries,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'category_id' => 'nullable|exists:transaction_categories,id',
            'date' => 'required|date',
            'receipt_photo' => 'nullable|image|max:5120',
            'notes' => 'nullable|string',
        ]);

        // Handle receipt upload
        $paymentData = $expense->payment_data ?? [];
        if ($request->hasFile('receipt_photo')) {
            if (!empty($paymentData['receipt_photo'])) {
                Storage::disk('public')->delete($paymentData['receipt_photo']);
            }
            $paymentData['receipt_photo'] = $request->file('receipt_photo')->store('receipts', 'public');
        }

        $expense->update([
            'ministry_id' => $validated['ministry_id'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
            'date' => $validated['date'],
            'notes' => $validated['notes'],
            'payment_data' => !empty($paymentData) ? $paymentData : null,
        ]);

        return redirect()->route('finances.expenses.index')
            ->with('success', 'Витрату оновлено.');
    }

    public function destroy(Transaction $expense)
    {
        $this->authorizeExpense($expense);
        if ($expense->ministry) {
            Gate::authorize('manage-ministry', $expense->ministry);
        }

        $paymentData = $expense->payment_data ?? [];
        if (!empty($paymentData['receipt_photo'])) {
            Storage::disk('public')->delete($paymentData['receipt_photo']);
        }

        $expense->delete();

        return back()->with('success', 'Витрату видалено.');
    }

    public function report(Request $request)
    {
        $church = $this->getCurrentChurch();

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        // By ministry
        $byMinistry = Ministry::where('church_id', $church->id)
            ->get()
            ->map(function ($m) use ($year, $month) {
                $spent = Transaction::where('ministry_id', $m->id)
                    ->where('direction', Transaction::DIRECTION_OUT)
                    ->forMonth($year, $month)
                    ->completed()
                    ->sum('amount');
                $percentage = $m->monthly_budget > 0
                    ? round(($spent / $m->monthly_budget) * 100, 1)
                    : 0;
                return [
                    'id' => $m->id,
                    'name' => $m->name,
                    'color' => $m->color,
                    'budget' => $m->monthly_budget,
                    'spent' => $spent,
                    'percentage' => $percentage,
                ];
            });

        // By category
        $byCategory = TransactionCategory::where('church_id', $church->id)
            ->where('type', 'expense')
            ->get()
            ->map(function ($cat) use ($church, $year, $month) {
                $cat->total = Transaction::where('church_id', $church->id)
                    ->where('category_id', $cat->id)
                    ->where('direction', Transaction::DIRECTION_OUT)
                    ->forMonth($year, $month)
                    ->completed()
                    ->sum('amount');
                return $cat;
            });

        // Total
        $totalSpent = Transaction::where('church_id', $church->id)
            ->where('direction', Transaction::DIRECTION_OUT)
            ->forMonth($year, $month)
            ->completed()
            ->sum('amount');

        $totalBudget = Ministry::where('church_id', $church->id)
            ->sum('monthly_budget');

        // Recent expenses
        $recentExpenses = Transaction::where('church_id', $church->id)
            ->where('direction', Transaction::DIRECTION_OUT)
            ->forMonth($year, $month)
            ->with(['ministry', 'category', 'recorder'])
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

    private function authorizeExpense(Transaction $expense): void
    {
        if ($expense->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
        if ($expense->direction !== Transaction::DIRECTION_OUT) {
            abort(404);
        }
    }

    private function calculateSpentThisMonth(int $churchId, array $ministryIds): float
    {
        return Transaction::where('church_id', $churchId)
            ->whereIn('ministry_id', $ministryIds)
            ->where('direction', Transaction::DIRECTION_OUT)
            ->thisMonth()
            ->completed()
            ->sum('amount');
    }
}
