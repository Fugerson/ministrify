<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        return redirect()->route('settings.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $validated['church_id'] = $this->getCurrentChurch()->id;
        ExpenseCategory::create($validated);

        return $this->successResponse($request, 'Категорію створено.');
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $this->authorizeChurch($expenseCategory);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $expenseCategory->update($validated);

        return $this->successResponse($request, 'Категорію оновлено.');
    }

    public function destroy(Request $request, ExpenseCategory $expenseCategory)
    {
        $this->authorizeChurch($expenseCategory);

        $expenseCategory->delete();

        return $this->successResponse($request, 'Категорію видалено.');
    }

    protected function authorizeChurch($model): void
    {
        if ($model->church_id !== $this->getCurrentChurch()->id) {
            abort(404);
        }
    }
}
