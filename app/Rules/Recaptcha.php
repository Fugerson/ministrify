<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Recaptcha implements ValidationRule
{
    public function __construct(
        protected string $action = 'submit'
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secretKey = config('services.recaptcha.secret_key');

        // Graceful degradation: skip if not configured
        if (empty($secretKey)) {
            return;
        }

        // Skip if no token provided (recaptcha script might not have loaded)
        if (empty($value)) {
            return;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secretKey,
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ]);

            if (!$response->successful()) {
                return; // API error — let the request through
            }

            $result = $response->json();

            if (!($result['success'] ?? false)) {
                Log::channel('security')->info('reCAPTCHA verification failed', [
                    'action' => $this->action,
                    'error_codes' => $result['error-codes'] ?? [],
                    'ip' => request()->ip(),
                ]);
                $fail('Перевірку безпеки не пройдено. Спробуйте ще раз.');
                return;
            }

            // Check action matches
            if (($result['action'] ?? '') !== $this->action) {
                Log::channel('security')->warning('reCAPTCHA action mismatch', [
                    'expected' => $this->action,
                    'actual' => $result['action'] ?? 'none',
                    'ip' => request()->ip(),
                ]);
                $fail('Перевірку безпеки не пройдено. Спробуйте ще раз.');
                return;
            }

            // Check score
            $threshold = (float) config('services.recaptcha.threshold', 0.5);
            $score = (float) ($result['score'] ?? 0);

            if ($score < $threshold) {
                Log::channel('security')->info('reCAPTCHA low score', [
                    'action' => $this->action,
                    'score' => $score,
                    'threshold' => $threshold,
                    'ip' => request()->ip(),
                ]);
                $fail('Перевірку безпеки не пройдено. Спробуйте ще раз.');
            }
        } catch (\Exception $e) {
            // Graceful degradation: if Google API is unreachable, let the request through
            Log::warning('reCAPTCHA API error: ' . $e->getMessage());
        }
    }
}
