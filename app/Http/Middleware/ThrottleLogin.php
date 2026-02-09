<?php

namespace App\Http\Middleware;

use App\Services\SecurityAlertService;
use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ThrottleLogin
{
    protected RateLimiter $limiter;
    protected SecurityAlertService $alertService;

    public function __construct(RateLimiter $limiter, SecurityAlertService $alertService)
    {
        $this->limiter = $limiter;
        $this->alertService = $alertService;
    }

    /**
     * Throttle login attempts to prevent brute force attacks.
     * Allows 5 attempts per minute per IP + email combination.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);
        $maxAttempts = 5;
        $decayMinutes = 1;

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $seconds = $this->limiter->availableIn($key);

            $maskedEmail = SecurityAlertService::maskEmail($request->input('email', '?'));

            $this->alertService->alert('brute_force', 'Brute force login attempt', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'details' => "Email: {$maskedEmail}, попыток: {$maxAttempts}+, блок на {$seconds}с",
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Забагато спроб входу. Спробуйте через {$seconds} секунд.",
                    'retry_after' => $seconds,
                ], 429);
            }

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => "Забагато спроб входу. Спробуйте через {$seconds} секунд.",
                ]);
        }

        $response = $next($request);

        // If login failed (redirected back or 422 status), increment attempts
        if ($response->getStatusCode() === 302 || $response->getStatusCode() === 422) {
            $this->limiter->hit($key, $decayMinutes * 60);
        } else {
            // Successful login - clear attempts
            $this->limiter->clear($key);
        }

        return $response;
    }

    /**
     * Create unique key from IP + email to prevent distributed attacks.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $email = strtolower($request->input('email', ''));
        return 'login:' . sha1($request->ip() . '|' . $email);
    }
}
