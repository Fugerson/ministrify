<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::where('church_id', $this->getCurrentChurch()->id)
            ->withCount('expenses')
            ->get();

        return view('settings.expense-categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $validated['church_id'] = $this->getCurrentChurch()->id;
        ExpenseCategory::create($validated);

        return back()->with('success', 'Категорію створено.');
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $this->authorizeChurch($expenseCategory);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $expenseCategory->update($validated);

        return back()->with('success', 'Категорію оновлено.');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        $this->authorizeChurch($expenseCategory);

        $expenseCategory->delete();

        return back()->with('success', 'Категорію видалено.');
    }

    protected function authorizeChurch($model): void
    {
        if ($model->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
