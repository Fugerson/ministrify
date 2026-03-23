<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait RespondsWithJson
{
    protected function successResponse(
        Request $request,
        string $message,
        ?string $redirectRoute = null,
        array $routeParams = [],
        array $data = []
    ): JsonResponse|RedirectResponse {
        if ($request->wantsJson()) {
            $response = ['success' => true, 'message' => $message, ...$data];
            if ($redirectRoute) {
                $response['redirect_url'] = route($redirectRoute, $routeParams);
            }

            return response()->json($response);
        }

        $redirect = $redirectRoute
            ? redirect()->route($redirectRoute, $routeParams)
            : back();

        return $redirect->with('success', $message);
    }

    protected function errorResponse(
        Request $request,
        string $message,
        int $statusCode = 422
    ): JsonResponse|RedirectResponse {
        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => $message], $statusCode);
        }

        return back()->with('error', $message);
    }
}
