<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SecurePassword implements ValidationRule
{
    /**
     * Strong password validation:
     * - Minimum 10 characters
     * - At least one uppercase letter
     * - At least one lowercase letter
     * - At least one number
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strlen($value) < 10) {
            $fail('Пароль повинен містити мінімум 10 символів.');
            return;
        }

        if (!preg_match('/[A-Z]/', $value)) {
            $fail('Пароль повинен містити хоча б одну велику літеру.');
            return;
        }

        if (!preg_match('/[a-z]/', $value)) {
            $fail('Пароль повинен містити хоча б одну малу літеру.');
            return;
        }

        if (!preg_match('/[0-9]/', $value)) {
            $fail('Пароль повинен містити хоча б одну цифру.');
            return;
        }

        // Check against common passwords
        $commonPasswords = [
            'password123', '12345678910', 'qwerty12345', 'admin12345',
            'welcome123', 'letmein123', 'changeme123', 'password1234',
        ];

        if (in_array(strtolower($value), $commonPasswords)) {
            $fail('Цей пароль занадто простий. Оберіть інший.');
            return;
        }
    }
}
