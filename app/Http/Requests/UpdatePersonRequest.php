<?php

namespace App\Http\Requests;

use App\Models\Person;
use App\Rules\BelongsToChurch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $personId = $this->route('person')?->id ?? $this->route('person');

        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('people')->where(function ($query) {
                    return $query->where('church_id', $this->user()->church_id);
                })->ignore($personId),
            ],
            'phone' => 'nullable|string|max:20',
            'gender' => ['nullable', Rule::in(array_keys(Person::GENDERS))],
            'marital_status' => ['nullable', Rule::in(array_keys(Person::MARITAL_STATUSES))],
            'birth_date' => 'nullable|date|before:today',
            'anniversary' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'telegram_username' => 'nullable|string|max:100',
            'photo' => 'nullable|image|max:5120',
            'membership_status' => ['nullable', Rule::in(array_keys(Person::MEMBERSHIP_STATUSES))],
            'church_role' => ['nullable', Rule::in(array_keys(Person::CHURCH_ROLES))],
            'first_visit_date' => 'nullable|date',
            'joined_date' => 'nullable|date',
            'baptism_date' => 'nullable|date',
            'notes' => 'nullable|string|max:5000',
            'shepherd_id' => ['nullable', 'exists:people,id', new BelongsToChurch(Person::class)],
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => "Ім'я обов'язкове",
            'last_name.required' => "Прізвище обов'язкове",
            'email.unique' => 'Цей email вже використовується',
            'birth_date.before' => 'Дата народження має бути в минулому',
            'photo.max' => 'Фото не може бути більше 5MB',
        ];
    }
}
