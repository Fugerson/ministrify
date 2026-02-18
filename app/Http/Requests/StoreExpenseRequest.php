<?php

namespace App\Http\Requests;

use App\Models\Ministry;
use App\Models\TransactionCategory;
use App\Rules\BelongsToChurch;
use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'exists:transaction_categories,id', new BelongsToChurch(TransactionCategory::class, 'expense')],
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'currency' => 'nullable|in:UAH,USD,EUR',
            'date' => 'required|date|before_or_equal:today',
            'ministry_id' => ['nullable', 'exists:ministries,id', new BelongsToChurch(Ministry::class)],
            'description' => 'required|string|max:255',
            'payment_method' => 'nullable|in:cash,card',
            'expense_type' => 'nullable|in:recurring,one_time',
            'notes' => 'nullable|string|max:5000',
            'force_over_budget' => 'boolean',
            'receipts' => 'nullable|array|max:10',
            'receipts.*' => 'file|mimes:jpg,jpeg,png,gif,webp,heic,heif,pdf|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Сума обов\'язкова',
            'amount.min' => 'Сума має бути більше 0',
            'date.required' => 'Дата обов\'язкова',
            'date.before_or_equal' => 'Дата не може бути в майбутньому',
            'description.required' => 'Опис обов\'язковий',
            'receipts.max' => 'Максимум 10 файлів',
            'receipts.*.max' => 'Файл не може бути більше 10MB',
        ];
    }
}
