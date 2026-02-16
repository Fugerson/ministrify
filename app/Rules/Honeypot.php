<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Honeypot implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Honeypot field must be empty — bots fill it in
        if (!empty($value)) {
            $fail('Форму не вдалося відправити.');
            return;
        }

        // Time-based check: form must exist for at least 2 seconds
        $started = request()->input('_hp_started');
        if ($started && is_numeric($started)) {
            $elapsed = time() - (int) $started;
            if ($elapsed < 2) {
                $fail('Форму не вдалося відправити.');
            }
        }
    }
}
