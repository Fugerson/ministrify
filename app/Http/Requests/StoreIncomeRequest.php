<?php

namespace App\Http\Requests;

use App\Models\Person;
use App\Models\TransactionCategory;
use App\Rules\BelongsToChurch;
use Illuminate\Foundation\Http\FormRequest;

class StoreIncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:transaction_categories,id', new BelongsToChurch(TransactionCategory::class, 'income')],
            'amount' => 'required|numeric|min:0.01|max:999999999.99',
            'currency' => 'nullable|in:UAH,USD,EUR',
            'date' => 'required|date|before_or_equal:today',
            'person_id' => ['nullable', 'exists:people,id', new BelongsToChurch(Person::class)],
            'description' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,card,transfer,online',
            'is_anonymous' => 'boolean',
            'notes' => 'nullable|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Категорія обов\'язкова',
            'amount.required' => 'Сума обов\'язкова',
            'amount.min' => 'Сума має бути більше 0',
            'date.required' => 'Дата обов\'язкова',
            'date.before_or_equal' => 'Дата не може бути в майбутньому',
            'payment_method.required' => 'Спосіб оплати обов\'язковий',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->boolean('is_anonymous')) {
            $this->merge(['person_id' => null]);
        }
    }
}
