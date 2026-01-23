<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
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
