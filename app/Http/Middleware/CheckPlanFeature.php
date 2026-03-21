<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $church = $request->attributes->get('currentChurch');

        if (!$church) {
            return $next($request);
        }

        if (!$church->hasFeature($feature)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('messages.plan_feature_unavailable'),
                    'upgrade_url' => route('landing.pricing'),
                ], 403);
            }

            abort(403, __('messages.plan_feature_unavailable'));
        }

        return $next($request);
    }
}
