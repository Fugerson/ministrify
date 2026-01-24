<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Handle CSRF token mismatch (419 Page Expired) - redirect to dashboard
        $this->renderable(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Сесія закінчилась. Оновіть сторінку.'], 419);
            }

            return redirect()->route('dashboard')->with('error', 'Сесія закінчилась. Спробуйте ще раз.');
        });

        // Handle expired/invalid verification links with a friendly page
        $this->renderable(function (InvalidSignatureException $e, $request) {
            // Check if this is a verification link
            if (str_contains($request->path(), 'email/verify')) {
                return response()->view('auth.verification-link-expired', [], 403);
            }

            // For other signed URLs, show generic error
            return response()->view('errors.403', [
                'message' => 'Посилання недійсне або застаріло.',
            ], 403);
        });
    }
}
