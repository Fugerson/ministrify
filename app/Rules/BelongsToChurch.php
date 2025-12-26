<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

/**
 * Validates that a related model belongs to the current user's church.
 *
 * Usage:
 *   'ministry_id' => ['required', new BelongsToChurch(Ministry::class)]
 *   'person_id' => ['nullable', new BelongsToChurch(Person::class)]
 *   'category_id' => ['required', new BelongsToChurch(TransactionCategory::class, 'expense')]
 */
class BelongsToChurch implements ValidationRule
{
    protected string $modelClass;
    protected ?string $type;
    protected string $message;

    public function __construct(string $modelClass, ?string $type = null)
    {
        $this->modelClass = $modelClass;
        $this->type = $type;
        $this->message = 'Обраний запис не належить вашій церкві.';
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return; // Let 'required' rule handle empty values
        }

        $user = auth()->user();
        if (!$user) {
            $fail($this->message);
            return;
        }

        // Get church ID (handle super admin impersonation)
        $churchId = $user->is_super_admin && session('impersonate_church_id')
            ? session('impersonate_church_id')
            : $user->church_id;

        if (!$churchId) {
            $fail($this->message);
            return;
        }

        // Find the model
        $model = $this->modelClass::find($value);

        if (!$model) {
            $fail('Обраний запис не знайдено.');
            return;
        }

        // Check church_id
        if (!isset($model->church_id) || $model->church_id !== $churchId) {
            $fail($this->message);
            return;
        }

        // Check type if specified (e.g., for TransactionCategory)
        if ($this->type && isset($model->type)) {
            if ($model->type !== $this->type && $model->type !== 'both') {
                $fail('Обрана категорія не підходить для цього типу операції.');
            }
        }
    }
}
